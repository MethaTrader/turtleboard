<?php

namespace Database\Seeders;

use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\QueryException;

class EmailAccountsTableSeeder extends Seeder
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

        // Create email accounts for each provider
        foreach (['Gmail', 'Outlook', 'Yahoo', 'iCloud'] as $provider) {
            // Create 5 accounts for each provider
            for ($i = 0; $i < 5; $i++) {
                try {
                    EmailAccount::factory()
                        ->provider($provider)
                        ->active()
                        ->create([
                            'user_id' => $users->random()->id,
                        ]);
                } catch (QueryException $e) {
                    // If it's a duplicate entry, try again with a different random string
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $this->command->info("Duplicate email detected, retrying for {$provider}...");
                        $i--; // Retry this iteration
                        continue;
                    }

                    // If it's another error, re-throw it
                    throw $e;
                }
            }
        }

        // Create some random email accounts
        for ($i = 0; $i < 10; $i++) {
            try {
                EmailAccount::factory()
                    ->create([
                        'user_id' => $users->random()->id,
                    ]);
            } catch (QueryException $e) {
                // If it's a duplicate entry, try again
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $this->command->info("Duplicate email detected, retrying...");
                    $i--; // Retry this iteration
                    continue;
                }

                // If it's another error, re-throw it
                throw $e;
            }
        }
    }
}