<?php

// app/Observers/Web3WalletObserver.php

namespace App\Observers;

use App\Models\Web3Wallet;
use App\Models\KpiTask;
use App\Services\ActivityService;
use App\Services\KpiGamificationService;
use Illuminate\Support\Facades\Auth;

class Web3WalletObserver
{
    protected $activityService;
    protected $kpiService;

    public function __construct(ActivityService $activityService, KpiGamificationService $kpiService)
    {
        $this->activityService = $activityService;
        $this->kpiService = $kpiService;
    }

    public function created(Web3Wallet $wallet): void
    {
        if (Auth::check()) {
            // Log the activity
            $this->activityService->logCreate($wallet, [
                'address' => $wallet->address,
                'network' => $wallet->network,
            ]);

            // Process KPI tasks and targets
            $this->processKpiRewards($wallet);
        }
    }

    public function updated(Web3Wallet $wallet): void
    {
        if (Auth::check()) {
            $this->activityService->logUpdate($wallet, [
                'address' => $wallet->address,
                'network' => $wallet->network,
                'changes' => $wallet->getChanges(),
            ]);
        }
    }

    public function deleted(Web3Wallet $wallet): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($wallet, [
                'address' => $wallet->address,
                'network' => $wallet->network,
            ]);
        }
    }

    public function forceDeleted(Web3Wallet $wallet): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($wallet, [
                'address' => $wallet->address,
                'network' => $wallet->network,
                'force_deleted' => true,
            ]);
        }
    }

    /**
     * Process KPI rewards for Web3 wallet creation.
     */
    protected function processKpiRewards(Web3Wallet $wallet): void
    {
        $user = $wallet->user;

        // Find and process Web3 wallet creation task
        $task = KpiTask::where('category', 'wallet_creation')
            ->where('active', true)
            ->first();

        if ($task) {
            $this->kpiService->processTaskCompletion($user, $task, $wallet);
        }

        // Check and update Web3 wallet targets
        $this->kpiService->checkTargetCompletion($user, 'web3_wallets');
    }
}