<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\Events\StockMovementRecorded;
use App\Jobs\LogStockMovementJob;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $req): JsonResponse
    {
    	//\Log::info(auth()->user());
        $movement = DB::transaction(function () use ($req) {
        	//\Log::info($req->validated());
            $query = StockMovement::query()
                ->selectRaw('product_id, warehouse_id, SUM(CASE WHEN type="in" THEN quantity ELSE -quantity END) as stock')
                ->where('product_id',$req->product_id)
                ->where('warehouse_id',$req->warehouse_id)
                ->groupBy('product_id','warehouse_id')
                ->first();

            if ($req->type === 'out') {
                $availableStock = $query?->stock ?? 0;

                if ($availableStock < $req->quantity) {
                    \Log::error('Quantity cannot exceed available stock for "out" type.');
                    throw ValidationException::withMessages([
                        'quantity' => ['Quantity cannot exceed available stock for "out" type.'],
                    ]);
                }
            }

            $m = StockMovement::create($req->validated());

            // Dispatch event and job(async log) after changes
            StockMovementRecorded::dispatch($m); ///Event with Listener
            
            LogStockMovementJob::dispatch($m->id); /// Job to log the stock movements

            return $m;
        });

        return response()->json($movement,201);
    }
}
