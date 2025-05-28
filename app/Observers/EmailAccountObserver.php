<?php

namespace App\Observers;

use App\Models\EmailAccount;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Auth;

class EmailAccountObserver
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Handle the EmailAccount "created" event.
     */
    public function created(EmailAccount $emailAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logCreate($emailAccount, [
                'email_address' => $emailAccount->email_address,
                'provider' => $emailAccount->provider,
            ]);
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
}