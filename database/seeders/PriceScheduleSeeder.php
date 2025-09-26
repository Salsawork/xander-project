<?php

namespace Database\Seeders;

use App\Models\PriceSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Testing\Fakes\Fake;

class PriceScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan venue dengan ID 1 dan 2 sudah ada
        if (!\App\Models\Venue::whereIn('id', [1, 2])->exists()) {
            $this->command->error('Venues with ID 1 and 2 not found. Please run VenueSeeder first.');
            return;
        }

        $schedules = [
            // Price schedule untuk venue 1 (8 Ball Pool Club)
            [
                'venue_id' => 1,
                'name' => 'Reguler Weekday',
                'start_time' => '10:00:00',
                'end_time' => '17:00:00',
                'days' => 'Weekday',
                'price' => 50000,
                'is_active' => true,
                'tables_applicable' => ['A01', 'A02', 'A03'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 1,
                'name' => 'Malam Weekday',
                'start_time' => '17:00:00',
                'end_time' => '22:00:00',
                'days' => 'Weekday',
                'price' => 70000,
                'is_active' => false,
                'tables_applicable' => ['A05', 'B01', 'B02'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 1,
                'name' => 'Weekend',
                'start_time' => '10:00:00',
                'end_time' => '22:00:00',
                'days' => 'Weekend',
                'price' => 80000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Price schedule untuk venue 2 (Billiard Master)
            [
                'venue_id' => 2,
                'name' => 'Siang',
                'start_time' => '11:00:00',
                'end_time' => '15:00:00',
                'days' => 'Everyday',
                'price' => 60000,
                'is_active' => false,
                'tables_applicable' => ['A01', 'A02', 'A03'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 2,
                'name' => 'Malam',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
                'days' => 'Everyday',
                'price' => 90000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($schedules as $schedule) {
            PriceSchedule::create($schedule);
        }

        $this->command->info('Price schedules seeded successfully!');
    }
}