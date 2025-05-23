<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\Web3Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with statistics.
     *
     * @return View
     */
    public function index(): View
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

        // Sample recent activities for the user (in a real app, this would come from a database)
        // These would be the specific activities related to the MEXC management system
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
        ]);
    }
}