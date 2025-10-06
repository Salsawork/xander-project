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
            'name' => 'Admin',
            'username' => 'admin@8ball.com',
            'email' => 'admin@8ball.com',
            'password' => Hash::make('password123'),
            'roles' => 'admin',
            'phone' => '081234567890',
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
            'name' => 'Alex Murphy',
            'username' => 'alex@athlete.com',
            'email' => 'alex@athlete.com',
            'password' => Hash::make('qweqwe'),
            'roles' => 'athlete',
            'phone' => '081234567896',
            'created_at' => now(),
            'updated_at' => now(),
            ],
        ]);
    }
}
