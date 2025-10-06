<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventSeeder extends Seeder
{
    public function run()
    {
        // Nonaktifkan foreign key check
        Schema::disableForeignKeyConstraints();
        
        // Hapus data yang ada sebelumnya
        DB::table('brackets')->truncate();
        DB::table('events')->truncate();
        
        // Aktifkan kembali foreign key check
        Schema::enableForeignKeyConstraints();

        $events = [
            [
                'name' => 'Masters of the Cue: National Billiards Championship 2025',
                'image_url' => '/images/event/event-2.png',
                'start_date' => '2025-06-15',
                'end_date' => '2025-06-17',
                'location' => 'Grand Arena, Los Angeles, CA',
                'game_types' => '9-Ball, 8-Ball, 10-Ball',
                'description' => 'Kompetisi billiard nasional terbesar tahun ini yang menampilkan pemain-pemain terbaik dari seluruh negeri. Acara ini akan menampilkan pertandingan sengit di berbagai kategori dan gaya permainan.',
                'total_prize_money' => 100000.00,
                'champion_prize' => 50000.00,
                'runner_up_prize' => 25000.00,
                'third_place_prize' => 15000.00,
                'match_style' => 'Double elimination rounds leading to knockout stages',
                'finals_format' => 'Best-of-15 racks for 9-ball and 10-ball, Best-of-17 for 8-ball',
                'divisions' => 'Open Division (Professional & Semi-Professional), Amateur Division',
                'social_media_handle' => '@MasterChampion_25',
                'status' => 'Upcoming',
            ],
            [
                'name' => 'Asian Billiards Grand Prix 2025',
                'image_url' => '/images/event/event-2.png',
                'start_date' => '2025-07-20',
                'end_date' => '2025-07-25',
                'location' => 'Marina Bay Sands, Singapore',
                'game_types' => '9-Ball, 10-Ball',
                'description' => 'Turnamen bergengsi yang mempertemukan bintang-bintang billiard terbaik Asia. Kompetisi ini menjadi ajang unjuk gigi para pemain profesional dari berbagai negara di Asia.',
                'total_prize_money' => 150000.00,
                'champion_prize' => 75000.00,
                'runner_up_prize' => 35000.00,
                'third_place_prize' => 20000.00,
                'match_style' => 'Round robin group stage followed by single elimination',
                'finals_format' => 'Best-of-17 racks for all matches',
                'divisions' => 'Men\'s Division, Women\'s Division',
                'social_media_handle' => '@AsianBilliardsGP',
                'status' => 'Upcoming',
            ],
            [
                'name' => 'World Pool Masters 2025',
                'image_url' => '/images/event/event-2.png',
                'start_date' => '2025-05-10',
                'end_date' => '2025-05-12',
                'location' => 'Mandalay Bay, Las Vegas, USA',
                'game_types' => '9-Ball',
                'description' => 'Turnamen kelas dunia yang hanya mengundang 16 pemain terbaik dunia. Kompetisi eksklusif ini menampilkan aksi-aksi spektakuler dari para master billiard dunia.',
                'total_prize_money' => 200000.00,
                'champion_prize' => 100000.00,
                'runner_up_prize' => 50000.00,
                'third_place_prize' => 25000.00,
                'match_style' => 'Single elimination tournament',
                'finals_format' => 'Best-of-21 racks for the final match',
                'divisions' => 'Invitational Only',
                'social_media_handle' => '@WorldPoolMasters',
                'status' => 'Ongoing',
            ]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}