<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\StockMovementRecorded;
use Illuminate\Support\Facades\Cache;

class InvalidateInventoryReportCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StockMovementRecorded $event): void
    {
        \Log::info('Listener is listening', [$event]);
        $productId = $event->movement->productId;
        $warehouseId = $event->movement->warehouseId;

        \Log::info('productId ', [$productId]);

        //Cache::tags(['inventory-report'])->flush();
        Cache::forget("inventory_report_{$productId}_{$warehouseId}");
        Cache::forget("inventory_report_{$productId}_all");
        Cache::forget("inventory_report_all_{$warehouseId}");
        Cache::forget("inventory_report_all_all");
    }
}
