<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'administrator',
        ]);

        // Create an account manager
        User::factory()->create([
            'name' => 'Account Manager',
            'email' => 'manager@example.com',
            'role' => 'account_manager',
        ]);

        // Create some random users
        User::factory()->count(5)->administrator()->create();
        User::factory()->count(10)->accountManager()->create();
    }
}