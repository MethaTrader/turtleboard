<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\Web3Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with statistics.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        // Fetch counts for each entity
        $mexcAccountsCount = MexcAccount::count();
        $emailAccountsCount = EmailAccount::count();
        $proxiesCount = Proxy::count();
        $web3WalletsCount = Web3Wallet::count();

        // Calculate active percentages
        $activeMexcAccounts = MexcAccount::where('status', 'active')->count();
        $activeEmailAccounts = EmailAccount::where('status', 'active')->count();

        $mexcActivePercentage = $mexcAccountsCount > 0
            ? round(($activeMexcAccounts / $mexcAccountsCount) * 100)
            : 0;

        $emailActivePercentage = $emailAccountsCount > 0
            ? round(($activeEmailAccounts / $emailAccountsCount) * 100)
            : 0;

        // Count connected Web3 wallets
        $connectedWallets = MexcAccount::whereNotNull('web3_wallet_id')->count();

        // Get email provider distribution
        $emailProviderStats = [
            'Gmail' => EmailAccount::where('provider', 'Gmail')->count(),
            'Outlook' => EmailAccount::where('provider', 'Outlook')->count(),
            'Yahoo' => EmailAccount::where('provider', 'Yahoo')->count(),
            'Rambler' => EmailAccount::where('provider', 'Rambler')->count(),
        ];

        // Sample recent activities for the user
        $userActivities = [
            [
                'type' => 'mexc',
                'action' => 'Added MEXC Account',
                'details' => 'wallet_34521@gmail.com',
                'status' => 'completed',
                'time' => '2 hours ago'
            ],
            [
                'type' => 'email',
                'action' => 'Created Email Account',
                'details' => 'new_account@outlook.com',
                'status' => 'completed',
                'time' => 'Yesterday at 9:15 AM'
            ],
            [
                'type' => 'web3',
                'action' => 'Connected Web3 Wallet',
                'details' => '0x742...8F31',
                'status' => 'pending',
                'time' => 'May 22, 2023 at 2:30 PM'
            ],
        ];

        // Check if user needs onboarding - session-based approach
        $needsOnboarding = false;

        // Check if this is first visit to dashboard
        if (!$request->session()->has('has_seen_tutorial')) {
            $needsOnboarding = true;

            // If the just_registered flag is set, keep it that way
            // Otherwise, mark that they've seen the tutorial now
            if (!$request->session()->has('just_registered')) {
                $request->session()->put('has_seen_tutorial', true);
            }
        }

        // If they just registered, they should see the tutorial
        if ($request->session()->has('just_registered')) {
            $needsOnboarding = true;

            // Clear the just_registered flag
            $request->session()->forget('just_registered');
            // Mark that they've seen the tutorial
            $request->session()->put('has_seen_tutorial', true);
        }

        // Database approach (if the columns exist)
        if (Schema::hasColumn('users', 'login_count') &&
            Schema::hasColumn('users', 'has_completed_onboarding')) {

            $user = $request->user();

            // First login or hasn't completed onboarding
            if ($user->login_count <= 1 || !$user->has_completed_onboarding) {
                $needsOnboarding = true;
            }
        }

        return view('dashboard', [
            'stats' => [
                'mexcAccounts' => $mexcAccountsCount,
                'emailAccounts' => $emailAccountsCount,
                'proxies' => $proxiesCount,
                'web3Wallets' => $web3WalletsCount,
                'activePercentages' => [
                    'mexc' => $mexcActivePercentage,
                    'email' => $emailActivePercentage,
                ],
                'connectedWallets' => $connectedWallets,
            ],
            'emailProviderStats' => $emailProviderStats,
            'userActivities' => $userActivities,
            'needsOnboarding' => $needsOnboarding, // Pass to the view
        ]);
    }
}