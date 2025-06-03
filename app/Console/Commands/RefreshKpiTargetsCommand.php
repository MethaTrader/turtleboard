<?php

namespace App\Console\Commands;

use App\Models\KpiTarget;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshKpiTargetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpi:refresh-targets {--force : Force refresh even if targets exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh KPI targets with current date ranges';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        // Get current date references
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $this->info('Refreshing KPI targets with current date ranges...');

        // Delete existing targets if force is used
        if ($force) {
            KpiTarget::truncate();
            $this->info('Cleared existing targets.');
        }

        // Weekly targets
        $weeklyTargets = [
            [
                'name' => 'Weekly MEXC Accounts',
                'description' => 'Create 10 MEXC accounts this week',
                'metric_type' => 'mexc_accounts',
                'target_value' => 10,
                'love_reward' => 100,
                'experience_reward' => 150,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
            ],
            [
                'name' => 'Weekly Email Accounts',
                'description' => 'Create 15 email accounts this week',
                'metric_type' => 'email_accounts',
                'target_value' => 15,
                'love_reward' => 75,
                'experience_reward' => 100,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
            ],
            [
                'name' => 'Weekly Proxies',
                'description' => 'Add 20 proxies this week',
                'metric_type' => 'proxies',
                'target_value' => 20,
                'love_reward' => 75,
                'experience_reward' => 100,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
            ],
            [
                'name' => 'Weekly Web3 Wallets',
                'description' => 'Create 5 Web3 wallets this week',
                'metric_type' => 'web3_wallets',
                'target_value' => 5,
                'love_reward' => 50,
                'experience_reward' => 75,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
            ],
        ];

        // Monthly targets
        $monthlyTargets = [
            [
                'name' => 'Monthly MEXC Accounts',
                'description' => 'Create 40 MEXC accounts this month',
                'metric_type' => 'mexc_accounts',
                'target_value' => 40,
                'love_reward' => 300,
                'experience_reward' => 500,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
            ],
            [
                'name' => 'Monthly Email Accounts',
                'description' => 'Create 50 email accounts this month',
                'metric_type' => 'email_accounts',
                'target_value' => 50,
                'love_reward' => 200,
                'experience_reward' => 350,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
            ],
            [
                'name' => 'Monthly Proxies',
                'description' => 'Add 60 proxies this month',
                'metric_type' => 'proxies',
                'target_value' => 60,
                'love_reward' => 200,
                'experience_reward' => 350,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
            ],
            [
                'name' => 'Monthly Web3 Wallets',
                'description' => 'Create 20 Web3 wallets this month',
                'metric_type' => 'web3_wallets',
                'target_value' => 20,
                'love_reward' => 150,
                'experience_reward' => 250,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
            ],
            [
                'name' => 'Task Completion Champion',
                'description' => 'Complete 30 tasks this month',
                'metric_type' => 'completed_tasks',
                'target_value' => 30,
                'love_reward' => 150,
                'experience_reward' => 250,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
            ],
        ];

        // Create weekly targets
        foreach ($weeklyTargets as $target) {
            // Check if target already exists for this week
            $existing = KpiTarget::where('name', $target['name'])
                ->where('start_date', $target['start_date'])
                ->where('end_date', $target['end_date'])
                ->first();

            if (!$existing) {
                KpiTarget::create($target);
                $this->info("Created weekly target: {$target['name']}");
            } elseif (!$force) {
                $this->warn("Weekly target already exists: {$target['name']}");
            }
        }

        // Create monthly targets
        foreach ($monthlyTargets as $target) {
            // Check if target already exists for this month
            $existing = KpiTarget::where('name', $target['name'])
                ->where('start_date', $target['start_date'])
                ->where('end_date', $target['end_date'])
                ->first();

            if (!$existing) {
                KpiTarget::create($target);
                $this->info("Created monthly target: {$target['name']}");
            } elseif (!$force) {
                $this->warn("Monthly target already exists: {$target['name']}");
            }
        }

        $this->info('KPI targets refresh completed!');
        $this->info("Week: {$weekStart->format('M d')} - {$weekEnd->format('M d, Y')}");
        $this->info("Month: {$monthStart->format('M d')} - {$monthEnd->format('M d, Y')}");
    }
}