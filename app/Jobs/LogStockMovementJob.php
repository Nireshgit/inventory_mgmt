<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\StockMovement;

class LogStockMovementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $movementId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $movement = StockMovement::with(['product','warehouse'])->findOrFail($this->movementId);

        \DB::table('stock_logs')->insert([
            'stock_movement_id' => $movement->id,
            'payload'           => $movement->toJson(),
            'logged_at'         => now(),
        ]);


    }
}
