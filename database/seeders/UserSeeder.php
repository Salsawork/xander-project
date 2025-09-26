<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Add users to the database.
     * The password for all users is 'password123'.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Venue Admin',
                'username' => 'admin@8ball.com',
                'password' => Hash::make('password123'),
                'roles' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Venue Admin 2',
                'username' => 'admin2@gmail.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Random User 1',
                'username' => 'user@8ball.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Venue Owners
            [
                'name' => 'Venue Owner 1',
                'username' => 'venue1@8ball.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'venue',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Venue Owner 2',
                'username' => 'venue2@8ball.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'venue',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // User Biasa
            [
                'name' => 'Random User 2',
                'username' => 'user2@8ball.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Athlete Users
            [
                'name' => 'Alex Murphy',
                'username' => 'alex@athlete.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'athlete',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jessica Lee',
                'username' => 'jessica@athlete.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'athlete',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Daniel Cruz',
                'username' => 'daniel@athlete.com',
                'password' => Hash::make('qweqwe'),
                'roles' => 'athlete',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
