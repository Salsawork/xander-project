<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            VenueSeeder::class,
            PriceScheduleSeeder::class,
            TableSeeder::class,
            BookingSeeder::class,
            VoucherSeeder::class,
            EventSeeder::class,
            BracketSeeder::class,
            MatchHistorySeeder::class,
            BilliardSessionSeeder::class,
            ParticipantSeeder::class,
            NewsSeeder::class,
            GuidelinesSeeder::class,
            AthleteDetailSeeder::class,
        ]);
    }
}
