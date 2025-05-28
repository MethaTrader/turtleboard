<?php
// Add this to app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\Web3Wallet;
use App\Observers\EmailAccountObserver;
use App\Observers\MexcAccountObserver;
use App\Observers\ProxyObserver;
use App\Observers\Web3WalletObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for activity tracking
        EmailAccount::observe(EmailAccountObserver::class);
        Proxy::observe(ProxyObserver::class);
        MexcAccount::observe(MexcAccountObserver::class);
        Web3Wallet::observe(Web3WalletObserver::class);
    }
}