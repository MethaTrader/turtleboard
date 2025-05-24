<?php

namespace App\Http\Controllers;

use App\Http\Requests\MexcAccountRequest;
use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Web3Wallet;
use App\Services\MexcAccountService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MexcAccountController extends Controller
{
    protected $mexcAccountService;

    public function __construct(MexcAccountService $mexcAccountService)
    {
        $this->mexcAccountService = $mexcAccountService;
    }

    /**
     * Display a listing of MEXC accounts.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = MexcAccount::query();

        // Filter by status if requested
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by search term (email)
        if ($request->has('search') && $request->search) {
            $query->whereHas('emailAccount', function($q) use ($request) {
                $q->where('email_address', 'like', '%' . $request->search . '%');
            });
        }

        // Default sorting by newest first
        $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'));

        $mexcAccounts = $query->with(['emailAccount', 'web3Wallet', 'user'])->paginate(10);

        // Count accounts by status
        $totalAccounts = MexcAccount::count();
        $activeAccounts = MexcAccount::where('status', 'active')->count();
        $inactiveAccounts = MexcAccount::where('status', 'inactive')->count();
        $suspendedAccounts = MexcAccount::where('status', 'suspended')->count();

        return view('mexc-accounts.index', [
            'mexcAccounts' => $mexcAccounts,
            'filters' => $request->only(['status', 'search']),
            'totalAccounts' => $totalAccounts,
            'activeAccounts' => $activeAccounts,
            'inactiveAccounts' => $inactiveAccounts,
            'suspendedAccounts' => $suspendedAccounts,
        ]);
    }

    /**
     * Show the form for creating a new MEXC account.
     *
     * @return View
     */
    public function create(): View
    {
        // Get available email accounts that are not already linked to a MEXC account
        $availableEmailAccounts = EmailAccount::whereDoesntHave('mexcAccount')
            ->where('status', 'active')
            ->get();

        // Get available Web3 wallets that are not already linked to a MEXC account
        $availableWeb3Wallets = Web3Wallet::whereDoesntHave('mexcAccount')->get();

        return view('mexc-accounts.create', [
            'availableEmailAccounts' => $availableEmailAccounts,
            'availableWeb3Wallets' => $availableWeb3Wallets,
        ]);
    }

    /**
     * Store a newly created MEXC account in storage.
     *
     * @param MexcAccountRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MexcAccountRequest $request)
    {
        try {
            $mexcAccount = $this->mexcAccountService->create($request->validated());
            return redirect()->route('accounts.mexc')
                ->with('success', 'MEXC account created successfully.')
                ->with('created_account_id', $mexcAccount->id);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error creating MEXC account: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified MEXC account.
     *
     * @param MexcAccount $mexcAccount
     * @return View
     */
    public function edit(MexcAccount $mexcAccount): View
    {
        // Get all available Web3 wallets (including the one currently assigned, if any)
        $availableWeb3Wallets = Web3Wallet::where(function ($query) use ($mexcAccount) {
            $query->whereDoesntHave('mexcAccount')
                ->orWhereHas('mexcAccount', function ($q) use ($mexcAccount) {
                    $q->where('id', $mexcAccount->id);
                });
        })->get();

        return view('mexc-accounts.edit', [
            'mexcAccount' => $mexcAccount,
            'availableWeb3Wallets' => $availableWeb3Wallets,
        ]);
    }

    /**
     * Update the specified MEXC account in storage.
     *
     * @param MexcAccountRequest $request
     * @param MexcAccount $mexcAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MexcAccountRequest $request, MexcAccount $mexcAccount)
    {
        try {
            $this->mexcAccountService->update($mexcAccount, $request->validated());
            return redirect()->route('accounts.mexc')->with('success', 'MEXC account updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating MEXC account: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified MEXC account from storage.
     *
     * @param MexcAccount $mexcAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MexcAccount $mexcAccount)
    {
        try {
            $this->mexcAccountService->delete($mexcAccount);
            return redirect()->route('accounts.mexc')->with('success', 'MEXC account deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting MEXC account: ' . $e->getMessage());
        }
    }

    /**
     * Get decrypted credentials for a MEXC account.
     *
     * @param MexcAccount $mexcAccount
     * @return \Illuminate\Http\JsonResponse
     */
    public function credentials(MexcAccount $mexcAccount)
    {
        try {
            return response()->json([
                'success' => true,
                'email' => $mexcAccount->emailAccount->email_address,
                'password' => $mexcAccount->password, // Automatically decrypted by the model
                'status' => $mexcAccount->status,
                'created_at' => $mexcAccount->created_at->format('M d, Y'),
                'web3_wallet' => $mexcAccount->web3Wallet ? $mexcAccount->web3Wallet->getFormattedAddress() : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving credentials: ' . $e->getMessage()
            ], 500);
        }
    }
}