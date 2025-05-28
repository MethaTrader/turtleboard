<?php
// app/Observers/ProxyObserver.php

namespace App\Observers;

use App\Models\Proxy;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Auth;

class ProxyObserver
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function created(Proxy $proxy): void
    {
        if (Auth::check()) {
            $this->activityService->logCreate($proxy, [
                'ip_address' => $proxy->ip_address,
                'port' => $proxy->port,
                'has_auth' => !empty($proxy->username),
            ]);
        }
    }

    public function updated(Proxy $proxy): void
    {
        if (Auth::check()) {
            $this->activityService->logUpdate($proxy, [
                'ip_address' => $proxy->ip_address,
                'port' => $proxy->port,
                'validation_status' => $proxy->validation_status,
                'changes' => $proxy->getChanges(),
            ]);
        }
    }

    public function deleted(Proxy $proxy): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($proxy, [
                'ip_address' => $proxy->ip_address,
                'port' => $proxy->port,
            ]);
        }
    }

    public function forceDeleted(Proxy $proxy): void
    {
        if (Auth::check()) {
            $this->activityService->logDelete($proxy, [
                'ip_address' => $proxy->ip_address,
                'port' => $proxy->port,
                'force_deleted' => true,
            ]);
        }
    }
}