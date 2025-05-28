<?php

// app/Observers/Web3WalletObserver.php

namespace App\Observers;

use App\Models\Web3Wallet;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Auth;

class Web3WalletObserver
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function created(Web3Wallet $wallet): void
    {
        if (Auth::check()) {
            $this->activityService->logCreate($wallet, [
                'address' => $wallet->address,
                'network' => $wallet->network,
            ]);
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
}