<?php
// app/Console/Commands/ProxyIPV4ManagementCommand.php

namespace App\Console\Commands;

use App\Services\ProxyIPV4Service;
use App\Models\Proxy;
use Illuminate\Console\Command;

class ProxyIPV4ManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:ipv4 
                            {action : The action to perform (test, refresh, sync, cleanup)}
                            {--force : Force the action without confirmation}
                            {--import-all : Import all available proxies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage ProxyIPV4 integration';

    protected $proxyIPV4Service;

    /**
     * Create a new command instance.
     */
    public function __construct(ProxyIPV4Service $proxyIPV4Service)
    {
        parent::__construct();
        $this->proxyIPV4Service = $proxyIPV4Service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'test':
                return $this->testConnection();
            case 'refresh':
                return $this->refreshProxies();
            case 'sync':
                return $this->syncProxies();
            case 'cleanup':
                return $this->cleanupExpired();
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: test, refresh, sync, cleanup');
                return 1;
        }
    }

    /**
     * Test ProxyIPV4 API connection.
     */
    protected function testConnection()
    {
        $this->info('Testing ProxyIPV4 API connection...');

        $result = $this->proxyIPV4Service->testConnection();

        if ($result['success']) {
            $this->info('✅ Connection successful!');
            if (isset($result['account_info'])) {
                $this->table(
                    ['Property', 'Value'],
                    collect($result['account_info'])->map(function ($value, $key) {
                        return [$key, is_array($value) ? json_encode($value) : $value];
                    })->toArray()
                );
            }
            return 0;
        } else {
            $this->error('❌ Connection failed: ' . $result['message']);
            return 1;
        }
    }

    /**
     * Refresh proxy list from ProxyIPV4.
     */
    protected function refreshProxies()
    {
        $this->info('Refreshing proxy list from ProxyIPV4...');

        // Clear cache first
        $this->proxyIPV4Service->clearCache();

        $result = $this->proxyIPV4Service->getPurchasedProxies();

        if ($result['success']) {
            $this->info("✅ Successfully fetched {$result['total']} proxies");

            // Display summary
            $activeCount = collect($result['proxies'])->where('is_active', true)->count();
            $expiredCount = collect($result['proxies'])->where('days_remaining', 0)->count();
            $expiringSoonCount = collect($result['proxies'])->filter(function($proxy) {
                return isset($proxy['days_remaining']) && $proxy['days_remaining'] <= 7 && $proxy['days_remaining'] > 0;
            })->count();

            $this->table(
                ['Status', 'Count'],
                [
                    ['Total', $result['total']],
                    ['Active', $activeCount],
                    ['Expired', $expiredCount],
                    ['Expiring Soon (≤7 days)', $expiringSoonCount],
                ]
            );

            return 0;
        } else {
            $this->error('❌ Failed to fetch proxies: ' . $result['message']);
            return 1;
        }
    }

    /**
     * Sync local ProxyIPV4 proxies with remote data.
     */
    protected function syncProxies()
    {
        $this->info('Syncing local ProxyIPV4 proxies with remote data...');

        // Get local ProxyIPV4 proxies
        $localProxies = Proxy::fromProxyIPV4()->get();
        $this->info("Found {$localProxies->count()} local ProxyIPV4 proxies");

        // Get remote proxies
        $result = $this->proxyIPV4Service->getPurchasedProxies();

        if (!$result['success']) {
            $this->error('❌ Failed to fetch remote proxies: ' . $result['message']);
            return 1;
        }

        $remoteProxies = collect($result['proxies']);
        $this->info("Found {$remoteProxies->count()} remote proxies");

        $updatedCount = 0;
        $markedExpiredCount = 0;

        foreach ($localProxies as $localProxy) {
            $proxyIPV4Data = $localProxy->getProxyIPV4Data();
            if (!$proxyIPV4Data || !isset($proxyIPV4Data['proxy_id'])) {
                continue;
            }

            $remoteProxy = $remoteProxies->firstWhere('id', $proxyIPV4Data['proxy_id']);

            if (!$remoteProxy) {
                $this->warn("Remote proxy not found for local proxy ID {$localProxy->id}");
                continue;
            }

            // Check if expired
            if (isset($remoteProxy['days_remaining']) && $remoteProxy['days_remaining'] === 0) {
                if ($localProxy->validation_status !== 'invalid') {
                    $localProxy->markAsInvalid();
                    $markedExpiredCount++;
                    $this->line("Marked proxy {$localProxy->ip_address}:{$localProxy->port} as invalid (expired)");
                }
            }

            // Update metadata if needed
            $currentMetadata = $localProxy->metadata ?? [];
            $needsUpdate = false;

            if ($remoteProxy['expiry_date'] &&
                (!isset($currentMetadata['expiry_date']) || $currentMetadata['expiry_date'] !== $remoteProxy['expiry_date']->toDateTimeString())) {
                $currentMetadata['expiry_date'] = $remoteProxy['expiry_date']->toDateTimeString();
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $localProxy->update(['metadata' => $currentMetadata]);
                $updatedCount++;
            }
        }

        $this->info("✅ Sync completed:");
        $this->table(
            ['Action', 'Count'],
            [
                ['Updated metadata', $updatedCount],
                ['Marked as expired', $markedExpiredCount],
            ]
        );

        // Optionally import new proxies
        if ($this->option('import-all')) {
            $this->info('Importing all available proxies...');
            $importedCount = $this->importAllProxies($remoteProxies, $localProxies);
            $this->info("✅ Imported {$importedCount} new proxies");
        }

        return 0;
    }

    /**
     * Clean up expired ProxyIPV4 proxies.
     */
    protected function cleanupExpired()
    {
        $this->info('Cleaning up expired ProxyIPV4 proxies...');

        $expiredProxies = Proxy::fromProxyIPV4()
            ->where('validation_status', 'invalid')
            ->whereRaw("JSON_EXTRACT(metadata, '$.expiry_date') < NOW()")
            ->get();

        if ($expiredProxies->isEmpty()) {
            $this->info('No expired proxies found to clean up.');
            return 0;
        }

        $this->info("Found {$expiredProxies->count()} expired proxies");

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to delete these expired proxies?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($expiredProxies as $proxy) {
            if ($proxy->isInUse()) {
                $this->warn("Skipping proxy {$proxy->ip_address}:{$proxy->port} (in use by {$proxy->emailAccount->email_address})");
                $skippedCount++;
                continue;
            }

            $proxy->delete();
            $deletedCount++;
            $this->line("Deleted expired proxy {$proxy->ip_address}:{$proxy->port}");
        }

        $this->info("✅ Cleanup completed:");
        $this->table(
            ['Action', 'Count'],
            [
                ['Deleted', $deletedCount],
                ['Skipped (in use)', $skippedCount],
            ]
        );

        return 0;
    }

    /**
     * Import all available proxies.
     */
    protected function importAllProxies($remoteProxies, $localProxies)
    {
        $localProxyIds = $localProxies->pluck('metadata.proxy_id')->filter()->toArray();
        $availableProxies = $remoteProxies->filter(function ($proxy) use ($localProxyIds) {
            return !in_array($proxy['id'], $localProxyIds) &&
                $proxy['is_active'] &&
                (!isset($proxy['days_remaining']) || $proxy['days_remaining'] > 0);
        });

        $importedCount = 0;

        foreach ($availableProxies as $proxy) {
            $result = $this->proxyIPV4Service->importProxy($proxy, 1); // Use admin user ID

            if ($result['success']) {
                $importedCount++;
                $this->line("Imported proxy {$proxy['ip_address']}:{$proxy['port']}");
            } else {
                $this->warn("Failed to import proxy {$proxy['ip_address']}:{$proxy['port']}: {$result['message']}");
            }
        }

        return $importedCount;
    }
}