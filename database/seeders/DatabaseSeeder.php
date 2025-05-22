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
            $rows[] = [
                'product_id'   => $productIds->random(),
                'warehouse_id' => $warehouseIds->random(),
                'quantity'     => rand(1, 25),
                'type'         => rand(0,1)?'in':'out',
                'movement_date'=> now()->subDays(rand(0, 365))->toDateString(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }
        
        $chunkSize = 500;

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            StockMovement::insert($chunk);
        }
    }
}
