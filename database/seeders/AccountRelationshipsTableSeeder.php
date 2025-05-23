<?php

namespace Database\Seeders;

use App\Models\AccountRelationship;
use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\User;
use App\Models\Web3Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Database\QueryException;

class AccountRelationshipsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing records that aren't already in relationships
        $proxies = Proxy::doesntHave('emailAccount')->get();
        $emailAccounts = EmailAccount::whereNull('proxy_id')->get();
        $mexcAccounts = MexcAccount::whereNull('web3_wallet_id')->get();
        $web3Wallets = Web3Wallet::doesntHave('mexcAccount')->get();

        // Get admin users
        $admins = User::where('role', 'administrator')->get();

        if ($admins->isEmpty()) {
            $this->command->info('No admin users found. Creating one...');
            $admins = [User::factory()->administrator()->create()];
        }

        // Create some relationships with existing entities
        $count = min(
            count($proxies),
            count($emailAccounts),
            count($mexcAccounts),
            count($web3Wallets)
        );

        $this->command->info("Creating {$count} account relationships...");

        for ($i = 0; $i < $count; $i++) {
            try {
                // First update the relationships in the individual models
                $emailAccounts[$i]->proxy_id = $proxies[$i]->id;
                $emailAccounts[$i]->save();

                $mexcAccounts[$i]->web3_wallet_id = $web3Wallets[$i]->id;
                $mexcAccounts[$i]->save();

                // Then create the relationship record
                AccountRelationship::create([
                    'proxy_id' => $proxies[$i]->id,
                    'email_account_id' => $emailAccounts[$i]->id,
                    'mexc_account_id' => $mexcAccounts[$i]->id,
                    'web3_wallet_id' => $web3Wallets[$i]->id,
                    'created_by' => $admins->random()->id,
                ]);

                $this->command->info("Created account relationship #" . ($i+1));
            } catch (QueryException $e) {
                $this->command->error("Error creating relationship #" . ($i+1) . ": " . $e->getMessage());
                // Continue with the next one
                continue;
            }
        }
    }
}