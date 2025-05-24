<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    public function report(Request $request): JsonResponse
    {
        $productId   = $request->integer('product_id');
        $warehouseId = $request->integer('warehouse_id');

        //$cacheKey = "report:".md5($productId.'|'.$warehouseId);
        $cacheKey = $this->generateCacheKey($productId, $warehouseId);

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($productId, $warehouseId) {
                return $this->buildReport($productId, $warehouseId);
            });

        //Cache will be available for 30 mins. Another req before 15 mins, will return cached value/data. If req between 15 to 30 min,  will return cached value/data and refresh the cached data. There is no req within 30 min, then cached data will be expired.
        // $data = Cache::flexible($cacheKey, [900, 1800], function () use ($productId, $warehouseId) {
        //         return $this->buildReport($productId, $warehouseId);
        //     }); 

        return response()->json($data);
    }

    private function buildReport(?int $productId, ?int $warehouseId): array
    {
        $query = StockMovement::query()
            ->selectRaw('product_id, warehouse_id, SUM(CASE WHEN type="in" THEN quantity ELSE -quantity END) as stock')
            ->groupBy('product_id','warehouse_id');

        if ($productId)   $query->where('product_id', $productId);
        if ($warehouseId) $query->where('warehouse_id',$warehouseId);

        // Use cursor to keep memory down for huge datasets
        return $query->cursor()->map(function ($row) {
            return [
                'product_id'   => $row->product_id,
                'warehouse_id' => $row->warehouse_id,
                'stock'        => (int) $row->stock,
            ];
        })->all();
    }

    protected function generateCacheKey($productId, $warehouseId)
	{
	    return 'inventory_report_' . ($productId ?: 'all') . '_' . ($warehouseId ?: 'all');
	}
}
