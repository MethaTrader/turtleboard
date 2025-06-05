{{-- resources/views/proxies/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-text-primary">Proxy Management</h2>
            <div class="flex space-x-3">
                <!-- NEW: ProxyIPV4 Button -->
                <a href="{{ route('accounts.proxy.proxy-ipv4') }}" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button flex items-center">
                    <i class="fas fa-cloud mr-2"></i>
                    <span>ProxyIPV4</span>
                </a>
                <button id="validateAllBtn" class="bg-success hover:bg-success/90 text-white py-2 px-4 rounded-button flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Validate Pending</span>
                </button>
                <a href="{{ route('accounts.proxy.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span>Add Proxies</span>
                </a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-card p-4 rounded-card shadow-card">
            <form action="{{ route('accounts.proxy') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="">All Statuses</option>
                        <option value="valid" {{ request('status') == 'valid' ? 'selected' : '' }}>Valid</option>
                        <option value="invalid" {{ request('status') == 'invalid' ? 'selected' : '' }}>Invalid</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div>
                    <label for="source" class="block text-sm font-medium text-text-secondary mb-1">Source</label>
                    <select id="source" name="source" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="">All Sources</option>
                        <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="proxy_ipv4" {{ request('source') == 'proxy_ipv4' ? 'selected' : '' }}>ProxyIPV4</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-text-secondary mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by IP or location" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                    <a href="{{ route('accounts.proxy') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Status Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-secondary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-server text-secondary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Total Proxies</p>
                    <p class="text-2xl font-bold text-text-primary">{{ $totalProxies }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-success/10 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-success text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Valid Proxies</p>
                    <p class="text-2xl font-bold text-success">{{ $validCount }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-warning/10 flex items-center justify-center mr-4">
                    <i class="fas fa-hourglass-half text-warning text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Pending Proxies</p>
                    <p class="text-2xl font-bold text-warning">{{ $pendingCount }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-danger/10 flex items-center justify-center mr-4">
                    <i class="fas fa-times-circle text-danger text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Invalid Proxies</p>
                    <p class="text-2xl font-bold text-danger">{{ $invalidCount }}</p>
                </div>
            </div>

            <!-- NEW: ProxyIPV4 Stats -->
            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-primary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-cloud text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">ProxyIPV4</p>
                    <p class="text-2xl font-bold text-primary">{{ $proxyIPV4Count }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-purple-100 flex items-center justify-center mr-4">
                    <i class="fas fa-hand-paper text-purple-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Manual</p>
                    <p class="text-2xl font-bold text-purple-500">{{ $manualCount }}</p>
                </div>
            </div>
        </div>

        <!-- Proxies Table -->
        <div class="bg-card rounded-card shadow-card overflow-hidden">
            @if(session('success'))
                <div class="bg-success/10 text-success p-4 border-l-4 border-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-warning/10 text-warning p-4 border-l-4 border-warning mb-4">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($proxies->isEmpty())
                <div class="p-6 text-center">
                    <div class="text-5xl text-gray-300 mb-4">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">No Proxies Found</h3>
                    <p class="text-text-secondary mb-4">You haven't added any proxies yet, or none match your filters.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="{{ route('accounts.proxy.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-plus mr-2"></i> Add Manual Proxies
                        </a>
                        <a href="{{ route('accounts.proxy.proxy-ipv4') }}" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-cloud mr-2"></i> Browse ProxyIPV4
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <form id="bulkValidationForm" action="{{ route('accounts.proxy.validate-all') }}" method="POST">
                        @csrf
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-secondary focus:ring-secondary/20">
                                        <label for="selectAll" class="ml-2 cursor-pointer">IP Address</label>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Port</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Auth</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Source</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Response Time</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Geolocation</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Last Validated</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">In Use</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($proxies as $proxy)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="ids[]" value="{{ $proxy->id }}" class="proxy-checkbox rounded border-gray-300 text-secondary focus:ring-secondary/20">
                                            <span class="ml-2 font-mono">{{ $proxy->ip_address }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-mono">{{ $proxy->port }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->username)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <i class="fas fa-user-check mr-1"></i> Yes
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                <i class="fas fa-user-times mr-1"></i> No
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Source Column -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->metadata && isset($proxy->metadata['source']) && $proxy->metadata['source'] === 'proxy_ipv4')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary/10 text-primary">
            <i class="fas fa-cloud mr-1"></i> ProxyIPV4
        </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
            <i class="fas fa-hand-paper mr-1"></i> Manual
        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->validation_status === 'valid')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-success/10 text-success">
                                                <i class="fas fa-check-circle mr-1"></i> Valid
                                            </span>
                                        @elseif($proxy->validation_status === 'invalid')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-danger/10 text-danger">
                                                <i class="fas fa-times-circle mr-1"></i> Invalid
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-warning/10 text-warning">
                                                <i class="fas fa-hourglass-half mr-1"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->response_time)
                                            <span class="text-text-primary">{{ $proxy->response_time }} ms</span>
                                        @else
                                            <span class="text-text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->geolocation)
                                            <div class="flex items-center">
                                                @if($proxy->country_code)
                                                    <img src="{{ $proxy->getFlagUrl() }}"
                                                         alt="{{ $proxy->country_code }}"
                                                         class="w-4 h-4 mr-2 rounded-sm"
                                                         onerror="this.style.display='none'">
                                                @endif
                                                <span class="text-text-primary">{{ $proxy->geolocation }}</span>
                                            </div>
                                        @else
                                            <span class="text-text-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->last_validation_date)
                                            <span class="text-text-primary">{{ $proxy->last_validation_date->diffForHumans() }}</span>
                                        @else
                                            <span class="text-text-secondary">Never</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($proxy->emailAccount)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <i class="fas fa-envelope mr-1"></i> {{ Str::limit($proxy->emailAccount->email_address, 20) }}
                                            </span>
                                        @else
                                            <span class="text-text-secondary">Not in use</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="copyProxyToClipboard('{{ $proxy->getFullProxyString() }}')"
                                                class="text-primary hover:text-primary/80 mr-3"
                                                title="Copy proxy">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <form action="{{ route('accounts.proxy.validate', $proxy) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-secondary hover:text-secondary/80 mr-3" title="Validate">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('accounts.proxy.edit', $proxy) }}" class="text-secondary hover:text-secondary/80 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('accounts.proxy.destroy', $proxy) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger hover:text-danger/80" title="Delete" onclick="return confirm('Are you sure you want to delete this proxy?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>

                <!-- Pagination -->
                <div class="p-4">
                    {{ $proxies->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Modal for Bulk Validation -->
    <div id="validationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <div class="text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-secondary mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-text-primary mb-2">Validating Proxies</h3>
                <p class="text-text-secondary mb-4">Please wait while we check your proxies. This may take a few minutes.</p>
                <p class="text-xs text-text-secondary">Do not close this window.</p>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Copy Actions -->
    <div id="copyToast" class="fixed top-4 right-0 bg-success text-white px-4 py-2 rounded-md shadow-lg transform translate-x-full transition-transform duration-300 z-50">
        <i class="fas fa-check mr-2"></i>
        <span id="copyToastMessage">Proxy copied to clipboard!</span>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all checkbox functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const proxyCheckboxes = document.querySelectorAll('.proxy-checkbox');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    proxyCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });
            }

            // Validate All button functionality
            const validateAllBtn = document.getElementById('validateAllBtn');
            const bulkValidationForm = document.getElementById('bulkValidationForm');
            const validationModal = document.getElementById('validationModal');

            if (validateAllBtn && bulkValidationForm) {
                validateAllBtn.addEventListener('click', function() {
                    // Check if any proxies are selected
                    const anySelected = Array.from(proxyCheckboxes).some(checkbox => checkbox.checked);

                    // If no proxies are selected, validate all pending proxies
                    if (!anySelected) {
                        if (confirm('No proxies selected. Do you want to validate all pending proxies?')) {
                            validationModal.classList.remove('hidden');
                            bulkValidationForm.submit();
                        }
                    } else {
                        // Validate selected proxies
                        if (confirm('Validate selected proxies?')) {
                            validationModal.classList.remove('hidden');
                            bulkValidationForm.submit();
                        }
                    }
                });
            }
        });

        // Copy proxy to clipboard function
        async function copyProxyToClipboard(proxyString) {
            const toast = document.getElementById('copyToast');
            const toastMessage = document.getElementById('copyToastMessage');

            try {
                await navigator.clipboard.writeText(proxyString);

                // Show toast
                toastMessage.textContent = 'Proxy copied to clipboard!';
                toast.classList.remove('translate-x-full');

                // Hide toast after 3 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                }, 3000);

            } catch (err) {
                console.error('Failed to copy:', err);

                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = proxyString;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    document.execCommand('copy');
                    toastMessage.textContent = 'Proxy copied to clipboard!';
                    toast.classList.remove('translate-x-full');

                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                    }, 3000);
                } catch (fallbackErr) {
                    toastMessage.textContent = 'Failed to copy to clipboard';
                    toast.classList.remove('translate-x-full');

                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                    }, 3000);
                }

                document.body.removeChild(textArea);
            }
        }
    </script>
@endpush