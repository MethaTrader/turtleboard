<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders in the correct order to maintain relationships
        $this->call([
            UsersTableSeeder::class,
            ProxiesTableSeeder::class,
            EmailAccountsTableSeeder::class,
            Web3WalletsTableSeeder::class,
            MexcAccountsTableSeeder::class,
            AccountRelationshipsTableSeeder::class,
            CompleteAccountChainSeeder::class,

            // Add KPI seeder to populate tasks, targets, and turtle items
            KpiSeeder::class,
        ]);
    }
}