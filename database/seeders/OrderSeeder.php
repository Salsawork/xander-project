<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Run the database seeds.
         */
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing records
        OrderItem::truncate();
        Order::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Get some random users and products
        $users = User::all();
        $products = Product::all();

        // Create 3 sample orders
        for ($i = 0; $i < 3; $i++) {
            // Generate UUID first
            $orderId = Str::uuid();

            // Create the order
            $order = Order::create([
                'id' => $orderId,
                'order_number' => 'ORD-' . date('ymd') . '-' . $i,
                'user_id' => $users->random()->id,
                'total' => 0, // Will be calculated later
                'payment_status' => ['pending', 'paid'][$i > 0 ? 1 : 0],
                'delivery_status' => ['pending', 'shipped', 'delivered'][$i],
                'payment_method' => ['bank_transfer', 'credit_card'][rand(0, 1)],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Add 2-3 random products to the order
            $randomProducts = $products->random(rand(2, 3));

            $total = 0;
            foreach ($randomProducts as $product) {
                $quantity = rand(1, 3);
                $subtotal = $quantity * $product->pricing;

                OrderItem::create([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->pricing,
                    'subtotal' => $subtotal,
                    'discount' => $product->discount,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $total += $subtotal;
            }

            // Update the order's total
            $order->update(['total' => $total]);
        }
    }
}
