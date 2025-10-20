<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecialUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * to run just type: php artisan db:seed --class=SpecialUsersSeeder
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'developer@example.com'],
            [
                'name' => 'Developer',
                'password' => bcrypt('password'),
                'role' => 'developer',
                'email_verified_at' => now(),
            ]
        );
        
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'system_admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
