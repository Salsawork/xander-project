<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan user dengan ID 4 dan 5 sudah ada
        if (!\App\Models\User::whereIn('id', [4, 5])->exists()) {
            $this->command->error('Users with ID 4 and 5 not found. Please run UserSeeder first.');
            return;
        }

        $venues = [
            [
                'user_id' => 4,
                'name' => '8 Ball Pool Club',
                'address' => 'Jl. Raya Kuta No. 123, Kuta, Bali',
                'phone' => '081234567890',
                'description' => 'Tempat nongkrong asik dengan meja billiard berkualitas dan suasana yang nyaman.',
                'operating_hours' => '10:00 - 22:00',
                'rating' => 4.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'name' => 'Billiard Master',
                'address' => 'Jl. Sudirman No. 45, Jakarta Selatan',
                'phone' => '081298765432',
                'description' => 'Tempat main billiard profesional dengan fasilitas lengkap dan meja berkualitas internasional.',
                'operating_hours' => '11:00 - 23:00',
                'rating' => 4.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }

        $this->command->info('Venues seeded successfully!');
    }
}