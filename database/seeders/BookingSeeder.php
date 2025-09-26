<?php

namespace Database\Seeders;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan data yang dibutuhkan sudah ada
        if (!\App\Models\User::whereIn('id', [3, 6])->exists() || 
            !\App\Models\Venue::whereIn('id', [1, 2])->exists()) {
            $this->command->error('Required users or venues not found. Please run UserSeeder and VenueSeeder first.');
            return;
        }

        $bookings = [];
        $now = now();
        $statusList = ['booked', 'cancelled', 'pending'];
        
        // Booking untuk user 3 (Random User 1)
        for ($i = 1; $i <= 15; $i++) {
            $bookings[] = [
                'venue_id' => 1,
                'table_id' => $i,
                'user_id' => 3,
                'booking_date' => $now->copy()->addDays(1)->format('Y-m-d'), // Besok
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'price' => 100000,
                'discount' => 0,
                'payment_method' => 'Cash',
                'status' => $statusList[array_rand($statusList)], // <-- ini yang bikin random!
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Booking untuk user 6 (Random User 2)
        for ($i = 1; $i <= 15; $i++) {
            $bookings[] = [
                'venue_id' => 2,
                'table_id' => $i,
                'user_id' => 6,
                'booking_date' => $now->copy()->addDays(1)->format('Y-m-d'), // Besok
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'price' => 120000,
                'discount' => 0,
                'payment_method' => 'Gopay',
                'status' => $statusList[array_rand($statusList)], // <-- ini yang bikin random!
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