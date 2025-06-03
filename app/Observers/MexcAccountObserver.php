<?php

// app/Observers/MexcAccountObserver.php

namespace App\Observers;

use App\Models\MexcAccount;
use App\Models\KpiTask;
use App\Services\ActivityService;
use App\Services\KpiGamificationService;
use Illuminate\Support\Facades\Auth;

class MexcAccountObserver
{
    protected $activityService;
    protected $kpiService;

    public function __construct(ActivityService $activityService, KpiGamificationService $kpiService)
    {
        $this->activityService = $activityService;
        $this->kpiService = $kpiService;
    }

    public function created(MexcAccount $mexcAccount): void
    {
        if (Auth::check()) {
            // Log the activity
            $this->activityService->logCreate($mexcAccount, [
                'email_address' => $mexcAccount->emailAccount->email_address,
                'has_wallet' => !empty($mexcAccount->web3_wallet_id),
            ]);

            // Process KPI tasks and targets
            $this->processKpiRewards($mexcAccount);
        }
    }

    public function updated(MexcAccount $mexcAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logUpdate($mexcAccount, [
                'email_address' => $mexcAccount->emailAccount->email_address,
                'status' => $mexcAccount->status,
                'changes' => $mexcAccount->getChanges(),
            ]);
        }
    }

    public function deleted(MexcAccount $mexcAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($mexcAccount, [
                'email_address' => $mexcAccount->emailAccount->email_address,
            ]);
        }
    }

    public function forceDeleted(MexcAccount $mexcAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($mexcAccount, [
                'email_address' => $mexcAccount->emailAccount->email_address,
                'force_deleted' => true,
            ]);
        }
    }

    /**
     * Process KPI rewards for MEXC account creation.
     */
    protected function processKpiRewards(MexcAccount $mexcAccount): void
    {
        $user = $mexcAccount->user;

        // Find and process MEXC account creation tasks
        $tasks = KpiTask::where('category', 'account_creation')
            ->where('active', true)
            ->get();

        foreach ($tasks as $task) {
            $this->kpiService->processTaskCompletion($user, $task, $mexcAccount);
        }

        // Check and update MEXC account targets
        $this->kpiService->checkTargetCompletion($user, 'mexc_accounts');

        // Check for first account creation achievement
        $this->checkFirstAccountCreation($user);
    }

    /**
     * Check if this is the user's first MEXC account for achievement.
     */
    protected function checkFirstAccountCreation($user): void
    {
        $accountCount = MexcAccount::where('user_id', $user->id)->count();

        if ($accountCount === 1) {
            // Find and process first account creation task
            $firstAccountTask = KpiTask::where('name', 'First MEXC Account')
                ->where('active', true)
                ->first();

            if ($firstAccountTask) {
                $this->kpiService->processTaskCompletion($user, $firstAccountTask);
            }
        }
    }
}