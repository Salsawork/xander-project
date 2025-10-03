<?php

namespace Database\Seeders;

use App\Models\MatchHistory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MatchHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan data yang dibutuhkan sudah ada
        if (!\App\Models\User::whereIn('id', [1, 2, 3, 4])->exists() || 
            !\App\Models\Venue::whereIn('id', [1, 2])->exists()) {
            $this->command->error('Required users or venues not found. Please run UserSeeder and VenueSeeder first.');
            return;
        }

        $matchHistories = [
            // Match histories untuk Alex Murphy (user_id 4)
            [
                'user_id' => 4,
                'venue_id' => 1,
                'opponent_id' => 2, // Random User 1
                'date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'time_start' => '12:00:00',
                'time_end' => '13:00:00',
                'payment_method' => 'Cash',
                'total_amount' => 500000,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'venue_id' => 1,
                'opponent_id' => 3, // Venue Owner 1
                'date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'time_start' => '18:00:00',
                'time_end' => '19:00:00',
                'payment_method' => 'DANA',
                'total_amount' => 70000,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'venue_id' => 1,
                'opponent_id' => 1, // Admin
                'date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'time_start' => '13:00:00',
                'time_end' => '14:00:00',
                'payment_method' => 'Gopay',
                'total_amount' => 50000,
                'status' => 'cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Match histories untuk Random User 1 (user_id 2)
            [
                'user_id' => 2,
                'venue_id' => 1,
                'opponent_id' => 4, // Alex Murphy
                'date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'time_start' => '12:00:00',
                'time_end' => '13:00:00',
                'payment_method' => 'Cash',
                'total_amount' => 90000,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'venue_id' => 1,
                'opponent_id' => 3, // Venue Owner 1
                'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'time_start' => '12:00:00',
                'time_end' => '13:00:00',
                'payment_method' => 'Cash',
                'total_amount' => 500000,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'venue_id' => 1,
                'opponent_id' => 4, // Alex Murphy
                'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'time_start' => '13:00:00',
                'time_end' => '14:00:00',
                'payment_method' => 'Gopay',
                'total_amount' => 50000,
                'status' => 'cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        foreach ($matchHistories as $history) {
            MatchHistory::create($history);
        }

        $this->command->info('Match histories seeded successfully!');
    }
}