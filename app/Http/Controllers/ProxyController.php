<?php

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
     * Display a listing of the proxies.
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
                $query->where('source', 'manual');
            } elseif ($request->source === 'proxy_ipv4') {
                $query->where('source', 'proxy_ipv4');
            }
        }

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('ip_address', 'like', '%' . $request->search . '%')
                    ->orWhere('geolocation', 'like', '%' . $request->search . '%');
            });
        }

        $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'));
        $proxies = $query->with('emailAccount')->paginate(15);

        // Calculate simple statistics
        $totalProxies = Proxy::count();
        $validCount = Proxy::where('validation_status', 'valid')->count();
        $invalidCount = Proxy::where('validation_status', 'invalid')->count();
        $pendingCount = Proxy::where('validation_status', 'pending')->count();
        $proxyIPV4Count = Proxy::where('source', 'proxy_ipv4')->count();
        $manualCount = Proxy::where('source', 'manual')->count();

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
                'stats' => array_fill_keys(['total', 'available', 'imported', 'used', 'expired', 'expiring_soon'], 0)
            ]);
        }

        // Get all imported ProxyIPV4 proxies
        $importedProxies = Proxy::where('source', 'proxy_ipv4')
            ->with(['emailAccount.user'])
            ->get()
            ->keyBy('proxy_ipv4_id');

        // Process each ProxyIPV4 proxy
        $proxies = [];
        foreach ($proxyIPV4Data['proxies'] as $proxy) {
            $proxyId = $proxy['id'] ?? null;
            $imported = $importedProxies->get($proxyId);

            $proxy['is_imported'] = $imported !== null;
            $proxy['is_used'] = $imported ? $imported->isInUse() : false;
            $proxy['used_by'] = $imported && $imported->emailAccount ? $imported->emailAccount->email_address : null;
            $proxy['used_by_user'] = $imported && $imported->emailAccount && $imported->emailAccount->user ? $imported->emailAccount->user->name : null;
            $proxy['local_proxy'] = $imported;

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
            $imported = $importedProxies->get($proxyId);

            if (!$imported && ($proxy['is_active'] ?? false)) {
                $stats['available']++;
            }
            if ($imported) {
                $stats['imported']++;
                if ($imported->isInUse()) {
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
            'proxies' => array_values($proxies),
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

        $purchasedProxies = $this->proxyIPV4Service->getPurchasedProxies();

        if (!$purchasedProxies['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get purchased proxies'
            ]);
        }

        $proxyToImport = collect($purchasedProxies['proxies'])->firstWhere('id', $request->proxy_id);

        if (!$proxyToImport) {
            return response()->json([
                'success' => false,
                'message' => 'Proxy not found in purchased proxies'
            ]);
        }

        try {
            // Check if proxy already exists (including soft deleted)
            $existing = Proxy::withTrashed()
                ->where('ip_address', $proxyToImport['ip_address'])
                ->where('port', $proxyToImport['port'])
                ->first();

            if ($existing && !$existing->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proxy already exists in local database'
                ]);
            }

            $dataToInsert = [
                'ip_address' => $proxyToImport['ip_address'],
                'port' => $proxyToImport['port'],
                'username' => $proxyToImport['username'] ?? null,
                'password' => $proxyToImport['password'] ?? null,
                'source' => 'proxy_ipv4',
                'proxy_ipv4_id' => $proxyToImport['id'],
                'purchase_date' => $proxyToImport['purchase_date'] ?? null,
                'expiry_date' => $proxyToImport['expiry_date'] ?? null,
                'protocol' => $proxyToImport['protocol'] ?? 'HTTP/HTTPS',
                'geolocation' => $proxyToImport['country'] ?? null,
                'country_code' => $proxyToImport['country_code'] ?? null,
                'validation_status' => 'pending',
                'user_id' => Auth::id(),
            ];

            if ($existing && $existing->trashed()) {
                // Restore the soft deleted proxy and update it
                $existing->restore();
                $existing->update($dataToInsert);

                return response()->json([
                    'success' => true,
                    'message' => 'Proxy restored and imported successfully',
                    'proxy' => $existing
                ]);
            }

            // Create new proxy
            $proxy = Proxy::create($dataToInsert);

            return response()->json([
                'success' => true,
                'message' => 'Proxy imported successfully',
                'proxy' => $proxy
            ]);

        } catch (\Exception $e) {
            \Log::error('ProxyIPV4 Import Error: ' . $e->getMessage(), [
                'proxy_data' => $proxyToImport,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import error: ' . $e->getMessage()
            ]);
        }
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
            $proxies = $this->proxyService->parseProxies(
                $request->input('proxy_list'),
                $request->file('proxy_file')
            );

            if ($proxies->isEmpty()) {
                return back()->with('error', 'No valid proxies found. Please check the format (IP:PORT:USERNAME:PASSWORD).');
            }

            $created = 0;
            $errors = [];

            foreach ($proxies as $proxyData) {
                try {
                    // Add source as manual for manually added proxies
                    $proxyData['source'] = 'manual';
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