<?php

namespace Database\Seeders;

use App\Models\Participants;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan session dan user yang dibutuhkan sudah ada
        if (!\App\Models\BilliardSession::whereIn('id', [1, 2, 3, 4, 5, 6])->exists() ||
            !\App\Models\User::whereIn('id', [3, 6, 7, 8, 9])->exists()) {
            $this->command->error('Required sessions or users not found. Please run BilliardSessionSeeder and UserSeeder first.');
            return;
        }

        $paymentMethods = ['Cash', 'DANA', 'Gopay', 'Credit Card', 'Bank Transfer'];
        $statuses = ['registered', 'attending', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'refunded'];

        $participants = [
            // Participants untuk session 1 (Morning Practice Session)
            [
                'session_id' => 1,
                'user_id' => 8, // Alex Murphy (athlete)
                'status' => 'attending',
                'payment_method' => 'Cash',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 1,
                'user_id' => 9, // Jessica Lee (athlete)
                'status' => 'attending',
                'payment_method' => 'DANA',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 1,
                'user_id' => 3, // Random User 1
                'status' => 'registered',
                'payment_method' => 'Gopay',
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Participants untuk session 2 (Afternoon Training)
            [
                'session_id' => 2,
                'user_id' => 9, // Jessica Lee (athlete)
                'status' => 'registered',
                'payment_method' => 'Credit Card',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 2,
                'user_id' => 6, // Random User 2
                'status' => 'registered',
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Participants untuk session 3 (Evening Tournament)
            [
                'session_id' => 3,
                'user_id' => 8, // Alex Murphy (athlete)
                'status' => 'registered',
                'payment_method' => 'Cash',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 3,
                'user_id' => 9, // Jessica Lee (athlete)
                'status' => 'registered',
                'payment_method' => 'DANA',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 3,
                'user_id' => 7, // Venue Owner 1
                'status' => 'registered',
                'payment_method' => 'Credit Card',
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Participants untuk session 4 (Pro Training)
            [
                'session_id' => 4,
                'user_id' => 8, // Alex Murphy (athlete)
                'status' => 'attending',
                'payment_method' => 'Credit Card',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 4,
                'user_id' => 9, // Jessica Lee (athlete)
                'status' => 'cancelled',
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'refunded',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Participants untuk session 5 (Casual Play)
            [
                'session_id' => 5,
                'user_id' => 3, // Random User 1
                'status' => 'registered',
                'payment_method' => 'Gopay',
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 5,
                'user_id' => 6, // Random User 2
                'status' => 'registered',
                'payment_method' => 'DANA',
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Participants untuk session 6 (Weekend Championship)
            [
                'session_id' => 6,
                'user_id' => 8, // Alex Murphy (athlete)
                'status' => 'registered',
                'payment_method' => 'Cash',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 6,
                'user_id' => 9, // Jessica Lee (athlete)
                'status' => 'registered',
                'payment_method' => 'Credit Card',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 6,
                'user_id' => 7, // Venue Owner 1
                'status' => 'registered',
                'payment_method' => 'DANA',
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 6,
                'user_id' => 3, // Random User 1
                'status' => 'registered',
                'payment_method' => 'Gopay',
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($participants as $participant) {
            Participants::create($participant);
        }

        $this->command->info('Participants seeded successfully!');
    }
}
