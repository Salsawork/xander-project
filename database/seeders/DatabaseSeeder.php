<?php

namespace Database\Seeders;

use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as FacadesDB;
use Pest\ArchPresets\Laravel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        FacadesDB::statement('SET FOREIGN_KEY_CHECKS=0;');
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
            LaravelTournamentSeeder::class,
        ]);
        FacadesDB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
