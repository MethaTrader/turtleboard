<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailAccountRequest;
use App\Models\EmailAccount;
use App\Models\Proxy;
use App\Services\EmailAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailAccountController extends Controller
{
    protected $emailAccountService;

    public function __construct(EmailAccountService $emailAccountService)
    {
        $this->emailAccountService = $emailAccountService;
    }

    /**
     * Display a listing of email accounts.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = EmailAccount::query();

        // Filter by provider if requested
        if ($request->has('provider') && $request->provider) {
            $query->where('provider', $request->provider);
        }

        // Filter by status if requested
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by search term (email address)
        if ($request->has('search') && $request->search) {
            $query->where('email_address', 'like', '%' . $request->search . '%');
        }

        // Default sorting by newest first
        $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'));

        $emailAccounts = $query->with('proxy')->paginate(10);
        $providers = EmailAccount::PROVIDERS;
        $availableProxies = Proxy::whereDoesntHave('emailAccount')->get();

        return view('email-accounts.index', [
            'emailAccounts' => $emailAccounts,
            'providers' => $providers,
            'availableProxies' => $availableProxies,
            'filters' => $request->only(['provider', 'status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new email account.
     *
     * @return View
     */
    public function create(): View
    {
        $providers = EmailAccount::PROVIDERS;
        $availableProxies = Proxy::whereDoesntHave('emailAccount')->get();

        return view('email-accounts.create', [
            'providers' => $providers,
            'availableProxies' => $availableProxies,
        ]);
    }

    /**
     * Store a newly created email account in storage.
     *
     * @param EmailAccountRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EmailAccountRequest $request)
    {
        try {
            $this->emailAccountService->create($request->validated());
            return redirect()->route('accounts.email')->with('success', 'Email account created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error creating email account: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified email account.
     *
     * @param EmailAccount $emailAccount
     * @return View
     */
    public function edit(EmailAccount $emailAccount): View
    {
        $providers = EmailAccount::PROVIDERS;
        $availableProxies = Proxy::where(function ($query) use ($emailAccount) {
            $query->whereDoesntHave('emailAccount')
                ->orWhereHas('emailAccount', function ($q) use ($emailAccount) {
                    $q->where('id', $emailAccount->id);
                });
        })->get();

        return view('email-accounts.edit', [
            'emailAccount' => $emailAccount,
            'providers' => $providers,
            'availableProxies' => $availableProxies,
        ]);
    }

    /**
     * Update the specified email account in storage.
     *
     * @param EmailAccountRequest $request
     * @param EmailAccount $emailAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EmailAccountRequest $request, EmailAccount $emailAccount)
    {
        try {
            $this->emailAccountService->update($emailAccount, $request->validated());
            return redirect()->route('accounts.email')->with('success', 'Email account updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating email account: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified email account from storage.
     *
     * @param EmailAccount $emailAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(EmailAccount $emailAccount)
    {
        try {
            $this->emailAccountService->delete($emailAccount);
            return redirect()->route('accounts.email')->with('success', 'Email account deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting email account: ' . $e->getMessage());
        }
    }
}