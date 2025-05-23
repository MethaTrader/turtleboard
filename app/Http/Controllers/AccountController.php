<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\Web3Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display a listing of all accounts.
     */
    public function index(): View
    {
        $emailAccounts = EmailAccount::with('user', 'proxy')->get();
        $mexcAccounts = MexcAccount::with('user', 'emailAccount', 'web3Wallet')->get();
        $proxies = Proxy::with('user', 'emailAccount')->get();
        $web3Wallets = Web3Wallet::with('user', 'mexcAccount')->get();

        return view('accounts.index', compact(
            'emailAccounts',
            'mexcAccounts',
            'proxies',
            'web3Wallets'
        ));
    }
}