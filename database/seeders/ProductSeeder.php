<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('products')->insert([
            [
                'name' => 'Pool Cue Stick',
                'description' => 'Professional billiard cue stick',
                'category_id' => 1,
                'brand' => 'Predator',
                'condition' => 'new',
                'quantity' => 10,
                'sku' => 'STICK-001',
                'images' => json_encode(['stick1.jpg', 'stick2.jpg']),
                'weight' => 567,
                'length' => 147,
                'breadth' => 3,
                'width' => 3,
                'pricing' => 299990,
                'discount' => 0.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Billiard Balls Set',
                'description' => 'Standard 16-ball billiard ball set',
                'category_id' => 2,
                'brand' => 'Mezz',
                'condition' => 'new',
                'quantity' => 5,
                'sku' => 'BALLS-001',
                'images' => json_encode(['balls1.jpg']),
                'weight' => 3200,
                'length' => 30,
                'breadth' => 30,
                'width' => 5,
                'pricing' => 89990,
                'discount' => 0.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Chalk Box',
                'description' => 'Premium billiard chalk box',
                'category_id' => 3,
                'brand' => 'Other',
                'condition' => 'new',
                'quantity' => 50,
                'sku' => 'CHALK-001',
                'images' => json_encode(['chalk1.jpg']),
                'weight' => 50,
                'length' => 5,
                'breadth' => 5,
                'width' => 5,
                'pricing' => 9990,
                'discount' => 0.00,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}
