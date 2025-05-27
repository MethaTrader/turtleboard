<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
        // Listen for login events to track user logins
        Event::listen(function (Login $event) {
            $user = $event->user;

            // Check if the columns exist before trying to update them
            if (Schema::hasColumn('users', 'login_count') &&
                Schema::hasColumn('users', 'last_login_at')) {

                $user->login_count = $user->login_count + 1;
                $user->last_login_at = now();
                $user->save();
            }
        });
    }
}