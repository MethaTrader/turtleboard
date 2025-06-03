<?php
// app/Observers/ProxyObserver.php

namespace App\Observers;

use App\Models\Proxy;
use App\Models\KpiTask;
use App\Services\ActivityService;
use App\Services\KpiGamificationService;
use Illuminate\Support\Facades\Auth;

class ProxyObserver
{
    protected $activityService;
    protected $kpiService;

    public function __construct(ActivityService $activityService, KpiGamificationService $kpiService)
    {
        $this->activityService = $activityService;
        $this->kpiService = $kpiService;
    }

    public function created(Proxy $proxy): void
    {
        if (Auth::check()) {
            // Log the activity
            $this->activityService->logCreate($proxy, [
                'ip_address' => $proxy->ip_address,
                'port' => $proxy->port,
                'has_auth' => !empty($proxy->username),
            ]);

            // Process KPI tasks and targets
            $this->processKpiRewards($proxy);
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

    /**
     * Process KPI rewards for proxy creation.
     */
    protected function processKpiRewards(Proxy $proxy): void
    {
        $user = $proxy->user;

        // Find and process proxy creation task
        $task = KpiTask::where('category', 'proxy_creation')
            ->where('active', true)
            ->first();

        if ($task) {
            $this->kpiService->processTaskCompletion($user, $task, $proxy);
        }

        // Check and update proxy targets
        $this->kpiService->checkTargetCompletion($user, 'proxies');
    }
}