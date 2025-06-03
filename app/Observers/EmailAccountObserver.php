<?php

namespace App\Observers;

use App\Models\EmailAccount;
use App\Models\KpiTask;
use App\Services\ActivityService;
use App\Services\KpiGamificationService;
use Illuminate\Support\Facades\Auth;

class EmailAccountObserver
{
    protected $activityService;
    protected $kpiService;

    public function __construct(ActivityService $activityService, KpiGamificationService $kpiService)
    {
        $this->activityService = $activityService;
        $this->kpiService = $kpiService;
    }

    /**
     * Handle the EmailAccount "created" event.
     */
    public function created(EmailAccount $emailAccount): void
    {
        if (Auth::check()) {
            // Log the activity
            $this->activityService->logCreate($emailAccount, [
                'email_address' => $emailAccount->email_address,
                'provider' => $emailAccount->provider,
            ]);

            // Process KPI tasks and targets
            $this->processKpiRewards($emailAccount);
        }
    }

    /**
     * Handle the EmailAccount "updated" event.
     */
    public function updated(EmailAccount $emailAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logUpdate($emailAccount, [
                'email_address' => $emailAccount->email_address,
                'provider' => $emailAccount->provider,
                'changes' => $emailAccount->getChanges(),
            ]);
        }
    }

    /**
     * Handle the EmailAccount "deleted" event.
     */
    public function deleted(EmailAccount $emailAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($emailAccount, [
                'email_address' => $emailAccount->email_address,
                'provider' => $emailAccount->provider,
            ]);
        }
    }

    /**
     * Handle the EmailAccount "force deleted" event.
     */
    public function forceDeleted(EmailAccount $emailAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($emailAccount, [
                'email_address' => $emailAccount->email_address,
                'provider' => $emailAccount->provider,
                'force_deleted' => true,
            ]);
        }
    }

    /**
     * Process KPI rewards for email account creation.
     */
    protected function processKpiRewards(EmailAccount $emailAccount): void
    {
        $user = $emailAccount->user;

        // Find and process email account creation task
        $task = KpiTask::where('category', 'email_creation')
            ->where('active', true)
            ->first();

        if ($task) {
            $this->kpiService->processTaskCompletion($user, $task, $emailAccount);
        }

        // Check and update email account targets
        $this->kpiService->checkTargetCompletion($user, 'email_accounts');
    }
}