<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockMovement;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        Warehouse::factory(10)->create();
        Product::factory(1000)->create();

        $warehouseIds = Warehouse::pluck('id');
        $productIds   = Product::pluck('id');

        $rows = [];
        for ($i = 0; $i < 10_000; $i++) {
            $productId = $productIds->random();
            $warehouseId = $warehouseIds->random();
            $key = $productId . '-' . $warehouseId;

            $maxOutQty = $stockLevels[$key] ?? 0;
            $type = rand(0, 1) ? 'in' : 'out';

            // If no stock, force type to 'in'
            if ($type === 'out' && $maxOutQty <= 0) {
                $type = 'in';
            }

            $qty = rand(1, 25);

            if ($type === 'out' && $qty > $maxOutQty) {
                $qty = $maxOutQty; // prevent going negative
            }

            // Adjust stock level
            $stockLevels[$key] = ($stockLevels[$key] ?? 0) + ($type === 'in' ? $qty : -$qty);

            $rows[] = [
                'product_id'    => $productId,
                'warehouse_id'  => $warehouseId,
                'quantity'      => $qty,
                'type'          => $type,
                'movement_date' => now()->subDays(rand(0, 365))->toDateString(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        
        $chunkSize = 500;

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            StockMovement::insert($chunk);
        }
    }
}
