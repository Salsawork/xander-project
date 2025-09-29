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
        DB::table('users')->truncate();

        DB::table('users')->insert([
            [
            'name' => 'Venue Admin',
            'username' => 'admin@8ball.com',
            'email' => 'admin@8ball.com',
            'password' => Hash::make('password123'),
            'roles' => 'admin',
            'phone' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'name' => 'Venue Admin 2',
            'username' => 'admin2@gmail.com',
            'email' => 'admin2@gmail.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'admin',
            'phone' => '081234567891',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'name' => 'Random User 1',
            'username' => 'user@8ball.com',
            'email' => 'user@8ball.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'user',
            'phone' => '081234567892',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            // Venue Owners
            [
            'name' => 'Venue Owner 1',
            'username' => 'venue1@8ball.com',
            'email' => 'venue1@8ball.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'venue',
            'phone' => '081234567893',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'name' => 'Venue Owner 2',
            'username' => 'venue2@8ball.com',
            'email' => 'venue2@8ball.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'venue',
            'phone' => '081234567894',
            'created_at' => now(),
            'updated_at' => now(),
            ],

            // User Biasa
            [
            'name' => 'Random User 2',
            'username' => 'user2@8ball.com',
            'email' => 'user2@8ball.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'user',
            'phone' => '081234567895',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            
            // Athlete Users
            [
            'name' => 'Alex Murphy',
            'username' => 'alex@athlete.com',
            'email' => 'alex@athlete.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'athlete',
            'phone' => '081234567896',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'name' => 'Jessica Lee',
            'username' => 'jessica@athlete.com',
            'email' => 'jessica@athlete.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'athlete',
            'phone' => '081234567897',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'name' => 'Daniel Cruz',
            'username' => 'daniel@athlete.com',
            'email' => 'daniel@athlete.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'athlete',
            'phone' => '081234567898',
            'created_at' => now(),
            'updated_at' => now(),
            ],
        ]);
    }
}
