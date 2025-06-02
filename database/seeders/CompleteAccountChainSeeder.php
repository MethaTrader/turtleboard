<?php

namespace Database\Seeders;

use App\Models\AccountRelationship;
use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\User;
use App\Models\Web3Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class CompleteAccountChainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin user
        $admin = User::where('role', 'administrator')->first();

        if (!$admin) {
            $admin = User::factory()->administrator()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        // Create 10 complete account chains
        $successCount = 0;
        $maxAttempts = 20; // Try up to 20 times to create 10 chains
        $attempt = 0;

        while ($successCount < 10 && $attempt < $maxAttempts) {
            $attempt++;

            try {
                // Create the entire chain in a transaction to ensure consistency
                \DB::transaction(function () use ($admin, &$successCount) {
                    // Create a proxy
                    $proxy = Proxy::factory()->valid()->create([
                        'user_id' => $admin->id,
                    ]);

                    // Create an email account with a guaranteed unique email
                    $randomEmail = 'user' . Str::random(10) . '@' . ['gmail.com', 'outlook.com', 'yahoo.com', 'icloud.com'][rand(0, 3)];

                    $emailAccount = EmailAccount::factory()->active()->create([
                        'user_id' => $admin->id,
                        'proxy_id' => $proxy->id,
                        'email_address' => $randomEmail,
                    ]);

                    // Create a wallet with a guaranteed unique address
                    $wallet = Web3Wallet::factory()->ethereum()->create([
                        'user_id' => $admin->id,
                        'address' => '0x' . Str::random(40), // Ensure uniqueness
                    ]);

                    // Create a MEXC account
                    $mexcAccount = MexcAccount::factory()->active()->create([
                        'email_account_id' => $emailAccount->id,
                        'user_id' => $admin->id,
                        'web3_wallet_id' => $wallet->id,
                    ]);

                    // Create the relationship record
                    AccountRelationship::create([
                        'proxy_id' => $proxy->id,
                        'email_account_id' => $emailAccount->id,
                        'mexc_account_id' => $mexcAccount->id,
                        'web3_wallet_id' => $wallet->id,
                        'created_by' => $admin->id,
                    ]);

                    $successCount++;
                });

                $this->command->info("Created complete account chain #" . $successCount);
            } catch (QueryException $e) {
                $this->command->error("Error creating chain: " . $e->getMessage());
                // Continue to the next attempt
                continue;
            }
        }

        $this->command->info("Successfully created {$successCount} complete account chains in {$attempt} attempts");
    }
}