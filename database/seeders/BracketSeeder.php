<?php

namespace Database\Seeders;

use App\Models\Bracket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BracketSeeder extends Seeder
{
    public function run()
    {
        // Nonaktifkan foreign key check
        Schema::disableForeignKeyConstraints();
        
        // Hapus data yang ada sebelumnya
        DB::table('brackets')->truncate();
        
        // Aktifkan kembali foreign key check
        Schema::enableForeignKeyConstraints();

        // Daftar pemain untuk diacak
        $players = [
            'Johnny Archer', 'Shane Van Boening', 'Efren Reyes', 'Francisco Bustamante',
            'Earl Strickland', 'Darren Appleton', 'Jayson Shaw', 'Ko Pin Yi',
            'Chang Jung-Lin', 'Carlo Biado', 'Alex Pagulayan', 'Dennis Orcollo',
            'Wu Jiaqing', 'Albin Ouschan', 'Niels Feijen', 'Ruslan Chinakhov',
            'Joshua Filler', 'Corey Deuel', 'Thorsten Hohmann', 'Mika Immonen',
            'Jeffrey De Luna', 'James Aranas', 'Roland Garcia', 'Anton Raga'
        ];

        // Buat bracket untuk setiap event (ID 1-3)
        for ($eventId = 1; $eventId <= 3; $eventId++) {
            // Acak urutan pemain
            shuffle($players);
            $eventPlayers = array_slice($players, 0, 16); // Ambil 16 pemain
            
            // Round 1 (16 pemain)
            $round1Winners = [];
            for ($i = 0; $i < 8; $i++) {
                $position = $i + 1;
                $isWinner = (bool)rand(0, 1); // Random pemenang
                
                // Player 1
                Bracket::create([
                    'event_id' => $eventId,
                    'player_name' => $eventPlayers[$i * 2],
                    'round' => 1,
                    'position' => $position * 2 - 1,
                    'next_match_position' => $position,
                    'is_winner' => $isWinner
                ]);

                // Player 2
                Bracket::create([
                    'event_id' => $eventId,
                    'player_name' => $eventPlayers[($i * 2) + 1],
                    'round' => 1,
                    'position' => $position * 2,
                    'next_match_position' => $position,
                    'is_winner' => !$isWinner
                ]);

                // Tentukan pemenang untuk round berikutnya
                $round1Winners[] = $isWinner ? $eventPlayers[$i * 2] : $eventPlayers[($i * 2) + 1];
            }

            // Round 2 (8 pemain)
            $round2Winners = [];
            for ($i = 0; $i < 4; $i++) {
                $position = $i + 1;
                $isWinner = (bool)rand(0, 1);
                
                // Player 1 (pemenang dari round 1)
                Bracket::create([
                    'event_id' => $eventId,
                    'player_name' => $round1Winners[$i * 2],
                    'round' => 2,
                    'position' => $position * 2 - 1,
                    'next_match_position' => $position,
                    'is_winner' => $isWinner
                ]);

                // Player 2 (pemenang dari round 1)
                Bracket::create([
                    'event_id' => $eventId,
                    'player_name' => $round1Winners[($i * 2) + 1],
                    'round' => 2,
                    'position' => $position * 2,
                    'next_match_position' => $position,
                    'is_winner' => !$isWinner
                ]);

                // Tentukan pemenang untuk semifinal
                $round2Winners[] = $isWinner ? $round1Winners[$i * 2] : $round1Winners[($i * 2) + 1];
            }

            // Round 3 (Semifinal - 4 pemain)
            $round3Winners = [];
            for ($i = 0; $i < 2; $i++) {
                $position = $i + 1;
                $isWinner = (bool)rand(0, 1);
                
                // Player 1 (pemenang dari round 2)
                Bracket::create([
                    'event_id' => $eventId,
                    'player_name' => $round2Winners[$i * 2],
                    'round' => 3,
                    'position' => $position * 2 - 1,
                    'next_match_position' => 1,
                    'is_winner' => $isWinner
                ]);

                // Player 2 (pemenang dari round 2)
                Bracket::create([
                    'event_id' => $eventId,
                    'player_name' => $round2Winners[($i * 2) + 1],
                    'round' => 3,
                    'position' => $position * 2,
                    'next_match_position' => 1,
                    'is_winner' => !$isWinner
                ]);

                // Tentukan pemenang untuk final
                $round3Winners[] = $isWinner ? $round2Winners[$i * 2] : $round2Winners[($i * 2) + 1];
            }

            // Round 4 (Final - 2 pemain)
            $isFinalWinner = (bool)rand(0, 1);
            
            // Finalist 1
            Bracket::create([
                'event_id' => $eventId,
                'player_name' => $round3Winners[0],
                'round' => 4,
                'position' => 1,
                'next_match_position' => null,
                'is_winner' => $isFinalWinner
            ]);

            // Finalist 2
            Bracket::create([
                'event_id' => $eventId,
                'player_name' => $round3Winners[1],
                'round' => 4,
                'position' => 2,
                'next_match_position' => null,
                'is_winner' => !$isFinalWinner
            ]);
        }
    }
}