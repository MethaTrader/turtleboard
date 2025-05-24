<?php
// app/Http/Controllers/ProxyController.php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use App\Http\Requests\SingleProxyRequest;
use App\Models\Proxy;
use App\Services\ProxyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProxyController extends Controller
{
    protected $proxyService;

    public function __construct(ProxyService $proxyService)
    {
        $this->proxyService = $proxyService;
    }

    /**
     * Display a listing of the proxies.
     */
    public function index(Request $request): View
    {
        $query = Proxy::query();

        // Filter by validation status if requested
        if ($request->has('status') && $request->status) {
            $query->where('validation_status', $request->status);
        }

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('ip_address', 'like', '%' . $request->search . '%')
                    ->orWhere('geolocation', 'like', '%' . $request->search . '%');
            });
        }

        // Default sorting by newest first
        $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'));

        $proxies = $query->with('emailAccount')->paginate(15);

        // Calculate status counts
        $totalProxies = Proxy::count();
        $validCount = Proxy::where('validation_status', 'valid')->count();
        $invalidCount = Proxy::where('validation_status', 'invalid')->count();
        $pendingCount = Proxy::where('validation_status', 'pending')->count();

        return view('proxies.index', [
            'proxies' => $proxies,
            'filters' => $request->only(['status', 'search']),
            'totalProxies' => $totalProxies,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Show the form for creating a new proxy.
     */
    public function create(): View
    {
        return view('proxies.create');
    }

    /**
     * Store a newly created proxy in storage.
     */
    public function store(ProxyRequest $request)
    {
        try {
            // Parse proxies from text input or file
            $proxies = $this->proxyService->parseProxies(
                $request->input('proxy_list'),
                $request->file('proxy_file')
            );

            if ($proxies->isEmpty()) {
                return back()->with('error', 'No valid proxies found. Please check the format (IP:PORT:USERNAME:PASSWORD).');
            }

            // Create proxies in the database
            $created = 0;
            $errors = [];

            foreach ($proxies as $proxyData) {
                try {
                    $this->proxyService->create($proxyData);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = $proxyData['ip_address'] . ':' . $proxyData['port'] . ' - ' . $e->getMessage();
                }
            }

            $message = "Successfully added {$created} proxies.";

            if (!empty($errors)) {
                $message .= " Failed to add " . count($errors) . " proxies.";
                return redirect()->route('accounts.proxy')->with('warning', $message);
            }

            return redirect()->route('accounts.proxy')->with('success', $message);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error adding proxies: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified proxy.
     */
    public function edit(Proxy $proxy): View
    {
        return view('proxies.edit', compact('proxy'));
    }

    /**
     * Update the specified proxy in storage.
     */
    public function update(SingleProxyRequest $request, Proxy $proxy)
    {
        try {
            $this->proxyService->update($proxy, $request->validated());
            return redirect()->route('accounts.proxy')->with('success', 'Proxy updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating proxy: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified proxy from storage.
     */
    public function destroy(Proxy $proxy)
    {
        try {
            $this->proxyService->delete($proxy);
            return redirect()->route('accounts.proxy')->with('success', 'Proxy deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting proxy: ' . $e->getMessage());
        }
    }

    /**
     * Validate a specific proxy.
     */
    public function validate(Proxy $proxy)
    {
        try {
            $isValid = $this->proxyService->validateProxy($proxy);
            $message = $isValid ? 'Proxy validated successfully.' : 'Proxy validation failed.';
            $status = $isValid ? 'success' : 'warning';

            return redirect()->route('accounts.proxy')->with($status, $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error validating proxy: ' . $e->getMessage());
        }
    }

    /**
     * Run validation on all pending proxies or selected ones.
     */
    public function runValidation(Request $request)
    {
        try {
            $query = Proxy::query();

            if ($request->has('ids') && is_array($request->ids) && !empty($request->ids)) {
                $query->whereIn('id', $request->ids);
            } else {
                $query->where('validation_status', 'pending');
            }

            $proxies = $query->get();

            if ($proxies->isEmpty()) {
                return back()->with('warning', 'No proxies found for validation.');
            }

            $results = $this->proxyService->bulkValidate($proxies);

            $message = "Validation completed: {$results['valid']} valid, {$results['invalid']} invalid out of {$results['total']} proxies.";
            return redirect()->route('accounts.proxy')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error running validation: ' . $e->getMessage());
        }
    }
}