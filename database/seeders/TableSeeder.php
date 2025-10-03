<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan venue dengan ID 1 sudah ada
        if (!\App\Models\Venue::where('id', 1)->exists()) {
            $this->command->error('Venue with ID 1 not found. Please run VenueSeeder first.');
            return;
        }

        $tables = [];
        
        // Buat 5 meja untuk venue 1 (8 Ball Pool Club)
        for ($i = 1; $i <= 5; $i++) {
            $tables[] = [
                'venue_id' => 1,
                'table_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
       // Buat 2 meja maintenance di venue 1
        $tables[] = [
            'venue_id' => 1,
            'table_number' => 'M01',
            'status' => 'booked',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        foreach ($tables as $table) {
            Table::create($table);
        }

        $this->command->info('Tables seeded successfully!');
    }
}