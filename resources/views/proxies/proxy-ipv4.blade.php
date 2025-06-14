{{-- resources/views/proxies/proxy-ipv4.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-text-primary">ProxyIPV4 Purchased Proxies</h2>
                <p class="text-text-secondary">Manage your purchased proxies from ProxyIPV4 service</p>
            </div>
            <div class="flex space-x-3">
                <button id="refreshProxiesBtn" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    <span>Refresh</span>
                </button>
                <button id="testConnectionBtn" class="bg-info hover:bg-info/90 text-white py-2 px-4 rounded-button flex items-center">
                    <i class="fas fa-plug mr-2"></i>
                    <span>Test Connection</span>
                </button>
                <a href="{{ route('accounts.proxy') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                    <i class="fas fa-arrow-left mr-2"></i> Back to All Proxies
                </a>
            </div>
        </div>

        <!-- API Status Alert -->
        @if(!$proxyIPV4Data['success'])
            <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">ProxyIPV4 API Error</h3>
                        <p class="text-sm mt-1">{{ $proxyIPV4Data['message'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters and Stats -->
        <div class="bg-card p-4 rounded-card shadow-card">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4 flex-1">
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                        <div class="text-xs text-blue-800">Total</div>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['available'] }}</div>
                        <div class="text-xs text-green-800">Available</div>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $stats['imported'] }}</div>
                        <div class="text-xs text-purple-800">Imported</div>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['used'] }}</div>
                        <div class="text-xs text-yellow-800">In Use</div>
                    </div>
                    <div class="text-center p-3 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $stats['expired'] }}</div>
                        <div class="text-xs text-red-800">Expired</div>
                    </div>
                    <div class="text-center p-3 bg-orange-50 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600">{{ $stats['expiring_soon'] }}</div>
                        <div class="text-xs text-orange-800">Expiring Soon</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex space-x-3 lg:ml-6">
                    <select id="filterSelect" class="rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="">All Proxies</option>
                        <option value="available" {{ request('filter') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="imported" {{ request('filter') == 'imported' ? 'selected' : '' }}>Imported</option>
                        <option value="used" {{ request('filter') == 'used' ? 'selected' : '' }}>In Use</option>
                        <option value="expired" {{ request('filter') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="expiring_soon" {{ request('filter') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                    </select>

                    <select id="sortSelect" class="rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="expiry_date" {{ request('sort') == 'expiry_date' ? 'selected' : '' }}>Sort by Expiry</option>
                        <option value="country" {{ request('sort') == 'country' ? 'selected' : '' }}>Sort by Country</option>
                        <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>Sort by Status</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Proxies Grid -->
        @if($proxyIPV4Data['success'] && !empty($proxies))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($proxies as $proxy)
                    <div class="proxy-card bg-card rounded-card shadow-card transition-all duration-300 hover:shadow-card-hover overflow-hidden">

                        <!-- Header with Status Indicators -->
                        <div class="p-4">
                            <!-- Country and Status -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    @if(isset($proxy['country_code']) && $proxy['country_code'])
                                        <img src="https://flagcdn.com/32x24/{{ strtolower($proxy['country_code']) }}.png"
                                             alt="{{ $proxy['country'] ?? 'Unknown' }}"
                                             class="w-8 h-6 rounded border border-gray-200"
                                             onerror="this.style.display='none'">
                                    @endif
                                    <span class="font-medium text-text-primary">{{ $proxy['country'] ?? 'Unknown' }}</span>
                                </div>

                                <!-- Status Badges -->
                                <div class="flex flex-wrap gap-1">
                                    @if($proxy['is_imported'])
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                            <i class="fas fa-download mr-1"></i>Imported
                        </span>
                                    @endif

                                    @if($proxy['is_used'])
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-user mr-1"></i>In Use
                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- IP and Port -->
                            <div class="mb-3">
                                <div class="flex items-center justify-between">
                                    <div class="font-mono text-text-primary font-semibold">
                                        {{ $proxy['ip_address'] }}:{{ $proxy['port'] }}
                                    </div>
                                    <button onclick="copyToClipboard('{{ $proxy['ip_address'] }}:{{ $proxy['port'] }}')"
                                            class="text-secondary hover:text-secondary/80 transition-colors"
                                            title="Copy IP:Port">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Authentication -->
                            @if(isset($proxy['username']) && $proxy['username'])
                                <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="text-xs text-text-secondary mb-1">Authentication</div>
                                    <div class="font-mono text-sm">
                                        <div>{{ $proxy['username'] }}</div>
                                        <div>{{ $proxy['password'] ?? '••••••••' }}</div>
                                    </div>
                                </div>
                            @endif

                            <!-- Usage Information - ENHANCED -->
                            @if($proxy['is_imported'])
                                <div class="mb-4 p-3 rounded-md {{ $proxy['is_used'] ? 'bg-yellow-50 border-l-4 border-yellow-500' : 'bg-purple-50 border-l-4 border-purple-500' }}">
                                    <div class="flex items-start">
                                        <i class="fas {{ $proxy['is_used'] ? 'fa-user-circle text-yellow-600' : 'fa-download text-purple-600' }} text-lg mr-2 mt-0.5"></i>
                                        <div class="flex-1">
                                            @if($proxy['is_used'])
                                                <div class="font-medium text-yellow-800">Currently in use</div>
                                                @if($proxy['used_by'])
                                                    <div class="text-yellow-700 font-medium">{{ $proxy['used_by'] }}</div>
                                                @endif
                                                @if($proxy['used_by_user'])
                                                    <div class="text-xs text-yellow-600 mt-1 flex items-center">
                                                        <i class="fas fa-user-tag mr-1"></i>
                                                        Manager: <span class="font-medium ml-1">{{ $proxy['used_by_user'] }}</span>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="font-medium text-purple-800">Imported but not in use</div>
                                                <div class="text-xs text-purple-600 mt-1">Available for email accounts</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Dates -->
                            @if(isset($proxy['expiry_date']) && $proxy['expiry_date'])
                                <div class="text-xs text-text-secondary mb-4">
                                    <div>Expires: {{ $proxy['expiry_date']->format('M d, Y') }}</div>
                                    @if(isset($proxy['days_remaining']))
                                        <div>({{ $proxy['days_remaining'] }} days remaining)</div>
                                    @endif
                                </div>
                            @endif

                            <!-- Action Button -->
                            @if(!$proxy['is_imported'] && ($proxy['is_active'] ?? false))
                                <button onclick="importProxy('{{ $proxy['id'] }}')"
                                        class="w-full bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button text-sm font-medium transition-colors">
                                    <i class="fas fa-download mr-2"></i>Import to Project
                                </button>
                            @elseif($proxy['is_imported'])
                                <div class="flex space-x-2">
                                    @if($proxy['local_proxy'])
                                        <a href="{{ route('accounts.proxy.edit', $proxy['local_proxy']->id) }}"
                                           class="flex-1 bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button text-sm font-medium text-center transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                    @endif
                                    <button class="flex-1 bg-gray-300 text-gray-600 py-2 px-4 rounded-button text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Imported
                                    </button>
                                </div>
                            @else
                                <button disabled class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-button text-sm font-medium cursor-not-allowed">
                                    <i class="fas fa-ban mr-2"></i>Not Available
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($proxyIPV4Data['success'] && empty($proxies))
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-filter text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-text-primary mb-2">No Proxies Found</h3>
                <p class="text-text-secondary mb-4">No proxies match your current filters.</p>
                <button onclick="clearFilters()" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                    Clear Filters
                </button>
            </div>
        @else
            <!-- Error State -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-text-primary mb-2">Failed to Load Proxies</h3>
                <p class="text-text-secondary mb-4">There was an error connecting to the ProxyIPV4 service.</p>
                <button onclick="location.reload()" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                    <i class="fas fa-refresh mr-2"></i>Try Again
                </button>
            </div>
        @endif
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <div class="text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-secondary mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-text-primary mb-2">Processing...</h3>
                <p class="text-text-secondary" id="loadingMessage">Please wait while we process your request.</p>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 bg-success text-white px-6 py-3 rounded-md shadow-lg transform translate-x-full transition-transform duration-300 z-50">
        <div class="flex items-center">
            <i class="fas fa-check mr-2"></i>
            <span id="toastMessage">Success!</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Filter and sort functionality
        document.getElementById('filterSelect').addEventListener('change', function() {
            updateUrl();
        });

        document.getElementById('sortSelect').addEventListener('change', function() {
            updateUrl();
        });

        function updateUrl() {
            const filter = document.getElementById('filterSelect').value;
            const sort = document.getElementById('sortSelect').value;
            const url = new URL(window.location);

            if (filter) {
                url.searchParams.set('filter', filter);
            } else {
                url.searchParams.delete('filter');
            }

            if (sort) {
                url.searchParams.set('sort', sort);
            } else {
                url.searchParams.delete('sort');
            }

            window.location.href = url.toString();
        }

        function clearFilters() {
            window.location.href = '{{ route("accounts.proxy.proxy-ipv4") }}';
        }

        // Copy to clipboard functionality
        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                showToast('Copied to clipboard!', 'success');
            } catch (err) {
                console.error('Failed to copy: ', err);
                showToast('Failed to copy to clipboard', 'error');
            }
        }

        // Password toggle functionality
        function togglePassword(proxyId, password) {
            const passwordElement = document.getElementById('password-' + proxyId);
            const eyeElement = document.getElementById('eye-' + proxyId);

            if (passwordElement.textContent === '••••••••') {
                passwordElement.textContent = password;
                eyeElement.className = 'fas fa-eye-slash text-xs';
            } else {
                passwordElement.textContent = '••••••••';
                eyeElement.className = 'fas fa-eye text-xs';
            }
        }

        // Import proxy functionality
        async function importProxy(proxyId) {
            showLoading('Importing proxy...');

            try {
                const response = await fetch('{{ route("accounts.proxy.import-ipv4") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ proxy_id: proxyId })
                });

                const result = await response.json();

                hideLoading();

                if (result.success) {
                    showToast('Proxy imported successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to import proxy', 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Network error occurred', 'error');
                console.error('Import error:', error);
            }
        }

        // Validate proxy functionality
        async function validateProxy(proxyId) {
            if (!proxyId) {
                showToast('Cannot validate: Proxy not found in system', 'error');
                return;
            }

            showLoading('Validating proxy...');

            try {
                const response = await fetch(`/accounts/proxy/${proxyId}/validate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                hideLoading();
                showToast('Proxy validation started', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } catch (error) {
                hideLoading();
                showToast('Validation error occurred', 'error');
                console.error('Validation error:', error);
            }
        }

        // Refresh proxies functionality
        document.getElementById('refreshProxiesBtn').addEventListener('click', async function() {
            showLoading('Refreshing proxy list...');

            try {
                const response = await fetch('{{ route("accounts.proxy.refresh-ipv4") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                hideLoading();

                if (result.success) {
                    showToast(`Refreshed! Found ${result.count} proxies`, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to refresh', 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Network error occurred', 'error');
                console.error('Refresh error:', error);
            }
        });

        // Test connection functionality
        document.getElementById('testConnectionBtn').addEventListener('click', async function() {
            showLoading('Testing API connection...');

            try {
                const response = await fetch('{{ route("accounts.proxy.test-ipv4") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                hideLoading();

                if (result.success) {
                    showToast('Connection successful!', 'success');
                } else {
                    showToast(result.message || 'Connection failed', 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Network error occurred', 'error');
                console.error('Connection test error:', error);
            }
        });

        // Utility functions
        function showLoading(message = 'Processing...') {
            document.getElementById('loadingMessage').textContent = message;
            document.getElementById('loadingModal').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingModal').classList.add('hidden');
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');

            toastMessage.textContent = message;

            // Update toast styling based on type
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg transform transition-transform duration-300 z-50 ${
                type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'
            }`;

            // Update icon
            const icon = toast.querySelector('i');
            icon.className = type === 'success' ? 'fas fa-check mr-2' : 'fas fa-times mr-2';

            // Show toast
            toast.classList.remove('translate-x-full');

            // Hide after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 3000);
        }
    </script>
@endpush