<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Web3Wallet;
use Illuminate\Database\Seeder;

class Web3WalletsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Creating some users first...');
            $users = User::factory()->count(5)->create();
        }

        // Create Ethereum wallets
        Web3Wallet::factory()
            ->count(15)
            ->ethereum()
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // Create Bitcoin wallets
        Web3Wallet::factory()
            ->count(10)
            ->bitcoin()
            ->create([
                'user_id' => $users->random()->id,
            ]);
    }
}