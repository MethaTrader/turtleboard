<?php
// app/Http/Controllers/ProxyController.php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use App\Http\Requests\SingleProxyRequest;
use App\Models\Proxy;
use App\Models\EmailAccount;
use App\Services\ProxyService;
use App\Services\ProxyIPV4Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ProxyController extends Controller
{
    protected $proxyService;
    protected $proxyIPV4Service;

    public function __construct(ProxyService $proxyService, ProxyIPV4Service $proxyIPV4Service)
    {
        $this->proxyService = $proxyService;
        $this->proxyIPV4Service = $proxyIPV4Service;
    }

    /**
     * Display a listing of the proxies with ProxyIPV4 integration.
     */
    public function index(Request $request): View
    {
        $query = Proxy::query();

        // Filter by validation status if requested
        if ($request->has('status') && $request->status) {
            $query->where('validation_status', $request->status);
        }

        // Filter by source (manual vs ProxyIPV4)
        if ($request->has('source')) {
            if ($request->source === 'manual') {
                $query->manuallyAdded();
            } elseif ($request->source === 'proxy_ipv4') {
                $query->fromProxyIPV4();
            }
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

// Simple count queries
        $proxyIPV4Count = Proxy::where('metadata->source', 'proxy_ipv4')->count();
        $manualCount = $totalProxies - $proxyIPV4Count;

        return view('proxies.index', [
            'proxies' => $proxies,
            'filters' => $request->only(['status', 'search', 'source']),
            'totalProxies' => $totalProxies,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
            'pendingCount' => $pendingCount,
            'proxyIPV4Count' => $proxyIPV4Count,
            'manualCount' => $manualCount,
        ]);
    }

    /**
     * Display ProxyIPV4 purchased proxies page.
     */
    public function proxyIPV4(Request $request): View
    {
        // Get purchased proxies from ProxyIPV4 service
        $proxyIPV4Data = $this->proxyIPV4Service->getPurchasedProxies();

        if (!$proxyIPV4Data['success']) {
            return view('proxies.proxy-ipv4', [
                'proxyIPV4Data' => $proxyIPV4Data,
                'proxies' => [],
                'filters' => $request->only(['filter', 'sort']),
                'stats' => [
                    'total' => 0,
                    'available' => 0,
                    'imported' => 0,
                    'used' => 0,
                    'expired' => 0,
                    'expiring_soon' => 0,
                ]
            ]);
        }

        // Get all imported ProxyIPV4 proxies with their relationships
        $importedProxies = Proxy::where('metadata->source', 'proxy_ipv4')
            ->with(['emailAccount.user'])
            ->get();

        // Create a simple mapping array
        $importedMap = [];
        foreach ($importedProxies as $proxy) {
            $proxyIPV4Id = $proxy->getProxyIPV4Id();
            if ($proxyIPV4Id) {
                $importedMap[$proxyIPV4Id] = [
                    'proxy' => $proxy,
                    'is_used' => $proxy->isInUse(),
                    'used_by' => $proxy->emailAccount ? $proxy->emailAccount->email_address : null,
                    'used_by_user' => $proxy->emailAccount && $proxy->emailAccount->user ? $proxy->emailAccount->user->name : null,
                ];
            }
        }

        // Process each ProxyIPV4 proxy
        $proxies = [];
        foreach ($proxyIPV4Data['proxies'] as $proxy) {
            $proxyId = $proxy['id'] ?? null;

            // Check if this proxy is imported
            $isImported = isset($importedMap[$proxyId]);

            $proxy['is_imported'] = $isImported;
            $proxy['is_used'] = false;
            $proxy['used_by'] = null;
            $proxy['used_by_user'] = null;
            $proxy['local_proxy'] = null;

            if ($isImported) {
                $importedData = $importedMap[$proxyId];
                $proxy['is_used'] = $importedData['is_used'];
                $proxy['used_by'] = $importedData['used_by'];
                $proxy['used_by_user'] = $importedData['used_by_user'];
                $proxy['local_proxy'] = $importedData['proxy'];
            }

            $proxies[] = $proxy;
        }

        // Apply filters
        if ($request->has('filter')) {
            $filter = $request->input('filter');
            $proxies = array_filter($proxies, function ($proxy) use ($filter) {
                switch ($filter) {
                    case 'available':
                        return !$proxy['is_imported'] && ($proxy['is_active'] ?? false);
                    case 'imported':
                        return $proxy['is_imported'];
                    case 'used':
                        return $proxy['is_imported'] && $proxy['is_used'];
                    case 'expired':
                        return ($proxy['days_remaining'] ?? null) === 0;
                    case 'expiring_soon':
                        $days = $proxy['days_remaining'] ?? null;
                        return $days !== null && $days <= 7 && $days > 0;
                    default:
                        return true;
                }
            });
        }

        // Calculate statistics
        $allProxies = $proxyIPV4Data['proxies'];
        $stats = [
            'total' => count($allProxies),
            'available' => 0,
            'imported' => 0,
            'used' => 0,
            'expired' => 0,
            'expiring_soon' => 0,
        ];

        foreach ($allProxies as $proxy) {
            $proxyId = $proxy['id'] ?? null;
            $isImported = isset($importedMap[$proxyId]);

            if (!$isImported && ($proxy['is_active'] ?? false)) {
                $stats['available']++;
            }
            if ($isImported) {
                $stats['imported']++;
                if ($importedMap[$proxyId]['is_used']) {
                    $stats['used']++;
                }
            }
            if (($proxy['days_remaining'] ?? null) === 0) {
                $stats['expired']++;
            }
            $days = $proxy['days_remaining'] ?? null;
            if ($days !== null && $days <= 7 && $days > 0) {
                $stats['expiring_soon']++;
            }
        }

        return view('proxies.proxy-ipv4', [
            'proxyIPV4Data' => $proxyIPV4Data,
            'proxies' => array_values($proxies), // Re-index array
            'filters' => $request->only(['filter', 'sort']),
            'stats' => $stats
        ]);
    }


    /**
     * Import a ProxyIPV4 proxy to local database.
     */
    public function importProxyIPV4(Request $request): JsonResponse
    {
        $request->validate([
            'proxy_id' => 'required|string',
        ]);

        // Get proxy details from ProxyIPV4 service
        $proxyDetails = $this->proxyIPV4Service->getProxyDetails($request->proxy_id);

        if (!$proxyDetails['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get proxy details from ProxyIPV4'
            ]);
        }

        // Get all purchased proxies to find the one we want to import
        $purchasedProxies = $this->proxyIPV4Service->getPurchasedProxies();

        if (!$purchasedProxies['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get purchased proxies'
            ]);
        }

        // Find the specific proxy
        $proxyToImport = collect($purchasedProxies['proxies'])->firstWhere('id', $request->proxy_id);

        if (!$proxyToImport) {
            return response()->json([
                'success' => false,
                'message' => 'Proxy not found in purchased proxies'
            ]);
        }

        // Import the proxy
        $result = $this->proxyIPV4Service->importProxy($proxyToImport, Auth::id());

        return response()->json($result);
    }

    /**
     * Refresh ProxyIPV4 proxy list.
     */
    public function refreshProxyIPV4(): JsonResponse
    {
        $this->proxyIPV4Service->clearCache();
        $proxyData = $this->proxyIPV4Service->getPurchasedProxies();

        return response()->json([
            'success' => $proxyData['success'],
            'message' => $proxyData['success'] ? 'Proxy list refreshed successfully' : 'Failed to refresh proxy list',
            'count' => $proxyData['success'] ? count($proxyData['proxies']) : 0
        ]);
    }

    /**
     * Test ProxyIPV4 API connection.
     */
    public function testProxyIPV4Connection(): JsonResponse
    {
        $result = $this->proxyIPV4Service->testConnection();
        return response()->json($result);
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