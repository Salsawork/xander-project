<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan data user dan venue ada
        if (!\App\Models\User::whereIn('id', [3, 6])->exists() ||
            !\App\Models\Venue::whereIn('id', [1, 2])->exists()) {
            $this->command->error('Required users or venues not found. Please run UserSeeder and VenueSeeder first.');
            return;
        }

        $now = now();
        $statusList = ['booked', 'cancelled', 'pending'];

        // Buat order dummy untuk user 3
        $orderUser3 = Order::firstOrCreate(
            ['user_id' => 3],
            [
                'id' => (string) Str::uuid(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total' => 0,
                'payment_status' => 'pending',
                'delivery_status' => 'pending',
                'payment_method' => 'Cash',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Buat order dummy untuk user 6
        $orderUser6 = Order::firstOrCreate(
            ['user_id' => 6],
            [
                'id' => (string) Str::uuid(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total' => 0,
                'payment_status' => 'pending',
                'delivery_status' => 'pending',
                'payment_method' => 'Gopay',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $bookings = [];

        // Booking untuk user 3 (Venue 1)
        for ($i = 1; $i <= 15; $i++) {
            $bookings[] = [
                'order_id' => $orderUser3->id,
                'venue_id' => 1,
                'table_id' => $i,
                'user_id' => 3,
                'booking_date' => $now->copy()->addDays(1)->format('Y-m-d'),
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'price' => 100000,
                'discount' => 0,
                'payment_method' => 'Cash',
                'status' => $statusList[array_rand($statusList)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Booking untuk user 6 (Venue 2)
        for ($i = 1; $i <= 15; $i++) {
            $bookings[] = [
                'order_id' => $orderUser6->id,
                'venue_id' => 2,
                'table_id' => $i,
                'user_id' => 6,
                'booking_date' => $now->copy()->addDays(1)->format('Y-m-d'),
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'price' => 120000,
                'discount' => 0,
                'payment_method' => 'Gopay',
                'status' => $statusList[array_rand($statusList)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($bookings as $booking) {
            Booking::create($booking);
        }

        $this->command->info('Bookings seeded successfully!');
    }
}
