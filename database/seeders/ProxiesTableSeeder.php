<?php

namespace Database\Seeders;

use App\Models\Proxy;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProxiesTableSeeder extends Seeder
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

        // Create valid proxies
        Proxy::factory()
            ->count(15)
            ->valid()
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // Create invalid proxies
        Proxy::factory()
            ->count(5)
            ->invalid()
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // Create pending proxies
        Proxy::factory()
            ->count(10)
            ->pending()
            ->create([
                'user_id' => $users->random()->id,
            ]);
    }
}