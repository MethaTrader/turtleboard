<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web3WalletRequest;
use App\Models\Web3Wallet;
use App\Services\Web3WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Web3WalletController extends Controller
{
    protected $web3WalletService;

    public function __construct(Web3WalletService $web3WalletService)
    {
        $this->web3WalletService = $web3WalletService;
    }

    /**
     * Display a listing of Web3 wallets.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Web3Wallet::query();

        // Filter by address if requested
        if ($request->has('search') && $request->search) {
            $query->where('address', 'like', '%' . $request->search . '%');
        }

        // Filter by connection status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'connected') {
                $query->whereHas('mexcAccount');
            } elseif ($request->status === 'unconnected') {
                $query->whereDoesntHave('mexcAccount');
            }
        }

        // Default sorting by newest first
        $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'));

        $wallets = $query->with('mexcAccount')->paginate(10);

        // Count wallets by status
        $totalWallets = Web3Wallet::count();
        $connectedWallets = Web3Wallet::whereHas('mexcAccount')->count();
        $unconnectedWallets = $totalWallets - $connectedWallets;

        return view('web3-wallets.index', [
            'wallets' => $wallets,
            'filters' => $request->only(['search', 'status']),
            'totalWallets' => $totalWallets,
            'connectedWallets' => $connectedWallets,
            'unconnectedWallets' => $unconnectedWallets,
        ]);
    }

    /**
     * Show the form for creating a new Web3 wallet.
     *
     * @return View
     */
    public function create(): View
    {
        return view('web3-wallets.create');
    }

    /**
     * Store a newly created Web3 wallet in storage.
     *
     * @param Web3WalletRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Web3WalletRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Verify we have required data for wallet creation
            if (empty($validatedData['address']) || empty($validatedData['seed_phrase'])) {
                throw new \InvalidArgumentException('Wallet address and seed phrase are required');
            }

            $wallet = $this->web3WalletService->create($validatedData);
            return redirect()->route('accounts.web3')
                ->with('success', 'Web3 wallet created successfully.')
                ->with('created_wallet_id', $wallet->id);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error creating Web3 wallet: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified Web3 wallet.
     *
     * @param Web3Wallet $web3Wallet
     * @return View
     */
    public function edit(Web3Wallet $web3Wallet): View
    {
        return view('web3-wallets.edit', [
            'wallet' => $web3Wallet
        ]);
    }

    /**
     * Update the specified Web3 wallet in storage.
     *
     * @param Web3WalletRequest $request
     * @param Web3Wallet $web3Wallet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Web3WalletRequest $request, Web3Wallet $web3Wallet)
    {
        try {
            $this->web3WalletService->update($web3Wallet, $request->validated());
            return redirect()->route('accounts.web3')->with('success', 'Web3 wallet updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating Web3 wallet: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified Web3 wallet from storage.
     *
     * @param Web3Wallet $web3Wallet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Web3Wallet $web3Wallet)
    {
        try {
            $this->web3WalletService->delete($web3Wallet);
            return redirect()->route('accounts.web3')->with('success', 'Web3 wallet deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting Web3 wallet: ' . $e->getMessage());
        }
    }

    /**
     * Get wallet details including decrypted seed phrase.
     *
     * @param Web3Wallet $web3Wallet
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Web3Wallet $web3Wallet)
    {
        try {
            return response()->json([
                'success' => true,
                'address' => $web3Wallet->address,
                'seed_phrase' => $web3Wallet->seed_phrase, // This will be automatically decrypted by the model's accessor
                'connected_to' => $web3Wallet->mexcAccount ? $web3Wallet->mexcAccount->emailAccount->email_address : null,
                'created_at' => $web3Wallet->created_at->format('M d, Y'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving wallet details: ' . $e->getMessage()
            ], 500);
        }
    }
}