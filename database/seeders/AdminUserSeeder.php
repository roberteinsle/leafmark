<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if doesn't exist
        User::firstOrCreate(
            ['email' => 'robert@einsle.com'],
            [
                'name' => 'Robert Einsle',
                'password' => Hash::make('password'), // Change this!
                'is_admin' => true,
            ]
        );
    }
}
