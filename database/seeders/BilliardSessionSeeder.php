<?php

namespace Database\Seeders;

use App\Models\BilliardSession;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BilliardSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan venue yang dibutuhkan sudah ada
        if (!\App\Models\Venue::whereIn('id', [1, 2])->exists()) {
            $this->command->error('Required venues not found. Please run VenueSeeder first.');
            return;
        }

        $gameTypes = ['9-Ball Match Rotation', 'Quick Play', '8-Ball Tournament', 'Training Session', 'Casual Game'];
        $skillLevels = ['Beginner', 'Intermediate', 'Advanced', 'Pro', 'All Levels'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        $sessions = [
            // Sessions untuk venue 1 (8 Ball Pool Club)
            [
                'venue_id' => 1,
                'title' => 'Morning Practice Session',
                'session_code' => 'XB' . strtoupper(substr(uniqid(), -8)),
                'game_type' => $gameTypes[0],
                'skill_level' => $skillLevels[2],
                'price' => 50000,
                'max_participants' => 5,
                'date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'promo_code' => 'MORNING10',
                'status' => $statuses[1], // confirmed
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 1,
                'title' => 'Afternoon Training',
                'session_code' => 'XB' . strtoupper(substr(uniqid(), -8)),
                'game_type' => $gameTypes[3],
                'skill_level' => $skillLevels[1],
                'price' => 60000,
                'max_participants' => 3,
                'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'start_time' => '14:00:00',
                'end_time' => '15:00:00',
                'promo_code' => null,
                'status' => $statuses[0], // pending
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 1,
                'title' => 'Evening Tournament',
                'session_code' => 'XB' . strtoupper(substr(uniqid(), -8)),
                'game_type' => $gameTypes[2],
                'skill_level' => $skillLevels[4],
                'price' => 100000,
                'max_participants' => 8,
                'date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'promo_code' => 'TOURNAMENT25',
                'status' => $statuses[0], // pending
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sessions untuk venue 2 (Billiard Master)
            [
                'venue_id' => 2,
                'title' => 'Pro Training',
                'session_code' => 'XB' . strtoupper(substr(uniqid(), -8)),
                'game_type' => $gameTypes[3],
                'skill_level' => $skillLevels[3],
                'price' => 150000,
                'max_participants' => 2,
                'date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'promo_code' => 'PRO20',
                'status' => $statuses[1], // confirmed
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 2,
                'title' => 'Casual Play',
                'session_code' => 'XB' . strtoupper(substr(uniqid(), -8)),
                'game_type' => $gameTypes[4],
                'skill_level' => $skillLevels[0],
                'price' => 40000,
                'max_participants' => 6,
                'date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'promo_code' => null,
                'status' => $statuses[0], // pending
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 2,
                'title' => 'Weekend Championship',
                'session_code' => 'XB' . strtoupper(substr(uniqid(), -8)),
                'game_type' => $gameTypes[2],
                'skill_level' => $skillLevels[4],
                'price' => 200000,
                'max_participants' => 16,
                'date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'promo_code' => 'WEEKEND30',
                'status' => $statuses[0], // pending
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sessions as $session) {
            BilliardSession::create($session);
        }

        $this->command->info('Billiard sessions seeded successfully!');
    }
}
