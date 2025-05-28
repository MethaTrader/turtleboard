<?php

// app/Observers/MexcAccountObserver.php

namespace App\Observers;

use App\Models\MexcAccount;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Auth;

class MexcAccountObserver
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function created(MexcAccount $mexcAccount): void
    {
        if (Auth::check()) {
            $this->activityService->logCreate($mexcAccount, [
                'email_address' => $mexcAccount->emailAccount->email_address,
                'has_wallet' => !empty($mexcAccount->web3_wallet_id),
            ]);
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
}