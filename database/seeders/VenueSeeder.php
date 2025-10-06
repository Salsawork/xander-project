<?php

namespace Database\Seeders;

use App\Models\Venue as ModelsVenue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Xoco70\LaravelTournaments\Models\Venue;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Venues seeding!');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('venues')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Pastikan user dengan ID 3 sudah ada
        if (!\App\Models\User::whereIn('id', [3])->exists()) {
            $this->command->error('Users with ID 3 not found. Please run UserSeeder first.');
            return;
        }

        ModelsVenue::create([
            'user_id' => 3,
            'name' => '8 Ball Pool Club',
            'address' => 'Jl. Raya Kuta No. 123, Kuta, Bali',
            'phone' => '081234567890',
            'description' => 'Tempat nongkrong asik dengan meja billiard berkualitas dan suasana yang nyaman.',
            'operating_hours' => '10:00 - 22:00',
            'rating' => 4.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Venues seeded successfully!');
    }
}
