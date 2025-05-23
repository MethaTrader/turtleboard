<?php

namespace Database\Seeders;

use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class MexcAccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing email accounts
        $emailAccounts = EmailAccount::doesntHave('mexcAccount')->get();

        if ($emailAccounts->isEmpty()) {
            $this->command->info('No available email accounts found. Creating some first...');
            $emailAccounts = EmailAccount::factory()->count(10)->create();
        }

        // Create MEXC accounts for existing email accounts
        foreach ($emailAccounts as $emailAccount) {
            MexcAccount::factory()->create([
                'email_account_id' => $emailAccount->id,
                'user_id' => $emailAccount->user_id,
            ]);
        }

        // Create some additional MEXC accounts with new email accounts
        MexcAccount::factory()
            ->count(5)
            ->active()
            ->create();
    }
}