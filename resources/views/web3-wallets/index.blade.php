@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-text-primary">Web3 Wallet Management</h2>
            <a href="{{ route('accounts.web3.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button flex items-center">
                <i class="fas fa-plus mr-2"></i>
                <span>Add Web3 Wallet</span>
            </a>
        </div>

        <!-- Filters Section -->
        <div class="bg-card p-4 rounded-card shadow-card">
            <form action="{{ route('accounts.web3') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Connection Status</label>
                    <select id="status" name="status" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="">All Wallets</option>
                        <option value="connected" {{ request('status') == 'connected' ? 'selected' : '' }}>Connected to MEXC</option>
                        <option value="unconnected" {{ request('status') == 'unconnected' ? 'selected' : '' }}>Not Connected</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-text-secondary mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by wallet address" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                    <a href="{{ route('accounts.web3') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Status Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-secondary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-wallet text-secondary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Total Wallets</p>
                    <p class="text-2xl font-bold text-text-primary">{{ $totalWallets }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-success/10 flex items-center justify-center mr-4">
                    <i class="fas fa-link text-success text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Connected to MEXC</p>
                    <p class="text-2xl font-bold text-success">{{ $connectedWallets }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-warning/10 flex items-center justify-center mr-4">
                    <i class="fas fa-unlink text-warning text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Not Connected</p>
                    <p class="text-2xl font-bold text-warning">{{ $unconnectedWallets }}</p>
                </div>
            </div>
        </div>

        <!-- Web3 Wallets Table -->
        <div class="bg-card rounded-card shadow-card overflow-hidden">
            @if(session('success'))
                <div class="bg-success/10 text-success p-4 border-l-4 border-success mb-4 flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    @if(session('created_wallet_id'))
                        <button onclick="viewWalletDetails({{ session('created_wallet_id') }})"
                                class="bg-success hover:bg-success/90 text-white py-1 px-3 rounded-button text-sm ml-4">
                            <i class="fas fa-eye mr-1"></i> View Details
                        </button>
                    @endif
                </div>
            @endif

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($wallets->isEmpty())
                <div class="p-6 text-center">
                    <div class="text-5xl text-gray-300 mb-4">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">No Web3 Wallets Found</h3>
                    <p class="text-text-secondary mb-4">You haven't added any Web3 wallets yet, or none match your filters.</p>
                    <a href="{{ route('accounts.web3.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                        <i class="fas fa-plus mr-2"></i> Add Your First Web3 Wallet
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Wallet Address
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Network
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Created
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($wallets as $wallet)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-link text-amber-800"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-text-primary font-mono">{{ $wallet->getFormattedAddress() }}</div>
                                            <div class="text-xs text-text-secondary">
                                                <button onclick="copyToClipboard('{{ $wallet->address }}')" class="text-secondary hover:text-secondary/80">
                                                    <i class="fas fa-copy mr-1"></i>Copy full address
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex-shrink-0 rounded-full flex items-center justify-center mr-3
                                            @if($wallet->network === 'ethereum')
                                                bg-blue-100
                                            @elseif($wallet->network === 'binance')
                                                bg-yellow-100
                                            @else
                                                bg-purple-100
                                            @endif">
                                            @if($wallet->network === 'ethereum')
                                                <i class="fab fa-ethereum text-blue-500 text-lg"></i>
                                            @elseif($wallet->network === 'binance')
                                                <i class="fas fa-coins text-yellow-600 text-lg"></i>
                                            @else
                                                <i class="fas fa-network-wired text-purple-500 text-lg"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-text-primary">
                                            {{ ucfirst($wallet->network) }}
                                            @if($wallet->network === 'binance')
                                                Smart Chain
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($wallet->mexcAccount)
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-success/10 text-success">
                                            <i class="fas fa-link mr-1"></i> Connected
                                        </span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-warning/10 text-warning">
                                            <i class="fas fa-unlink mr-1"></i> Not Connected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                    {{ $wallet->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="viewWalletDetails({{ $wallet->id }})"
                                            class="text-primary hover:text-primary/80 mr-3"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('accounts.web3.edit', $wallet) }}" class="text-secondary hover:text-secondary/80 mr-3" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if(!$wallet->mexcAccount)
                                        <form action="{{ route('accounts.web3.destroy', $wallet) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger hover:text-danger/80" title="Delete" onclick="return confirm('Are you sure you want to delete this wallet?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="text-gray-400 cursor-not-allowed" title="Cannot delete a connected wallet">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4">
                    {{ $wallets->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Wallet Details Modal -->
    <div id="walletDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-text-primary">Wallet Details</h3>
                    <button onclick="closeWalletDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-secondary text-2xl mb-2"></i>
                    <p class="text-text-secondary">Loading wallet details...</p>
                </div>

                <!-- Modal Content -->
                <div id="modalContent" class="hidden">
                    <!-- Network Display -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Network</label>
                        <div id="modalNetworkDisplay" class="flex items-center p-3 bg-gray-50 rounded-md">
                            <!-- Network icon and name will be populated via JavaScript -->
                        </div>
                    </div>

                    <!-- Address Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Wallet Address</label>
                        <div class="relative">
                            <input type="text" id="modalAddress" disabled readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary font-mono">
                            <button onclick="copyToClipboard(document.getElementById('modalAddress').value)"
                                    class="absolute right-2 top-2 text-gray-400 hover:text-secondary">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Seed Phrase Field -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Seed Phrase</label>
                        <div class="relative">
                            <textarea id="modalSeedPhrase" disabled readonly
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary font-mono" rows="3"></textarea>
                            <div class="absolute right-2 top-2 flex space-x-1">
                                <button onclick="toggleSeedPhraseVisibility()"
                                        class="text-gray-400 hover:text-secondary">
                                    <i class="fas fa-eye" id="seedPhraseToggleIcon"></i>
                                </button>
                                <button onclick="copyToClipboard(document.getElementById('modalSeedPhrase').value)"
                                        class="text-gray-400 hover:text-secondary">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-danger">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Never share your seed phrase with anyone. Keep it secure!
                        </div>
                    </div>

                    <!-- Connection Status Field -->
                    <div class="mb-6" id="connectionStatusContainer">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Connection Status</label>
                        <div id="connectionStatus" class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary"></div>
                    </div>

                    <!-- Close Button -->
                    <div class="flex justify-end">
                        <button onclick="closeWalletDetailsModal()"
                                class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-times mr-2"></i> Close
                        </button>
                    </div>
                </div>

                <!-- Error State -->
                <div id="modalError" class="hidden text-center py-8">
                    <i class="fas fa-exclamation-triangle text-danger text-2xl mb-2"></i>
                    <p class="text-danger">Failed to load wallet details. Please try again.</p>
                    <button onclick="closeWalletDetailsModal()"
                            class="mt-4 bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Copy Actions -->
    <div id="copyToast" class="fixed top-4 right-0 bg-success text-white px-4 py-2 rounded-md shadow-lg transform translate-x-full transition-transform duration-300 z-50">
        <i class="fas fa-check mr-2"></i>
        <span id="copyToastMessage">Copied to clipboard!</span>
    </div>
@endsection

@push('scripts')
    <script>
        // Store current wallet data for modal display
        let currentWalletData = null;

        // View wallet details
        async function viewWalletDetails(walletId) {
            const modal = document.getElementById('walletDetailsModal');
            const loading = document.getElementById('modalLoading');
            const content = document.getElementById('modalContent');
            const error = document.getElementById('modalError');
            const connectionStatusContainer = document.getElementById('connectionStatusContainer');

            // Show modal and loading state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            error.classList.add('hidden');

            // Hide seed phrase initially
            hideSeedPhrase();

            try {
                const response = await fetch(`/accounts/web3/${walletId}/details`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch wallet details');
                }

                const data = await response.json();
                currentWalletData = data; // Store for reference

                // Populate modal with data
                document.getElementById('modalAddress').value = data.address;
                document.getElementById('modalSeedPhrase').value = data.seed_phrase;

                // Display network with icon
                displayNetworkInModal(data.network);

                // Set connection status
                const connectionStatus = document.getElementById('connectionStatus');
                if (data.connected_to) {
                    connectionStatus.innerHTML = `<span class="text-success font-medium">
                        <i class="fas fa-link mr-1"></i> Connected to MEXC account: ${data.connected_to}
                    </span>`;
                    connectionStatusContainer.classList.remove('hidden');
                } else {
                    connectionStatus.innerHTML = `<span class="text-warning font-medium">
                        <i class="fas fa-unlink mr-1"></i> Not connected to any MEXC account
                    </span>`;
                    connectionStatusContainer.classList.remove('hidden');
                }

                // Show content
                loading.classList.add('hidden');
                content.classList.remove('hidden');

            } catch (err) {
                console.error('Error fetching wallet details:', err);
                loading.classList.add('hidden');
                error.classList.remove('hidden');
            }
        }

        // Display network information in modal
        function displayNetworkInModal(network) {
            const networkDisplay = document.getElementById('modalNetworkDisplay');

            let iconClass, bgClass, networkName;

            switch(network) {
                case 'ethereum':
                    iconClass = 'fab fa-ethereum text-blue-500';
                    bgClass = 'bg-blue-100';
                    networkName = 'Ethereum';
                    break;
                case 'binance':
                    iconClass = 'fas fa-coins text-yellow-600';
                    bgClass = 'bg-yellow-100';
                    networkName = 'Binance Smart Chain';
                    break;
                default:
                    iconClass = 'fas fa-network-wired text-purple-500';
                    bgClass = 'bg-purple-100';
                    networkName = 'Unknown Network';
            }

            networkDisplay.innerHTML = `
                <div class="w-8 h-8 flex-shrink-0 rounded-full ${bgClass} flex items-center justify-center mr-3">
                    <i class="${iconClass} text-lg"></i>
                </div>
                <span class="text-text-primary font-medium">${networkName}</span>
            `;
        }

        // Close wallet details modal
        function closeWalletDetailsModal() {
            const modal = document.getElementById('walletDetailsModal');
            modal.classList.add('hidden');

            // Reset seed phrase visibility
            hideSeedPhrase();
            currentWalletData = null;
        }

        // Hide seed phrase
        function hideSeedPhrase() {
            const seedPhraseField = document.getElementById('modalSeedPhrase');
            const toggleIcon = document.getElementById('seedPhraseToggleIcon');

            // Replace text with asterisks
            if (seedPhraseField.value) {
                const words = seedPhraseField.value.split(' ');
                const maskedWords = words.map(word => '*'.repeat(word.length));
                seedPhraseField.dataset.original = seedPhraseField.value;
                seedPhraseField.value = maskedWords.join(' ');
            }

            // Reset icon
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }

        // Toggle seed phrase visibility
        function toggleSeedPhraseVisibility() {
            const seedPhraseField = document.getElementById('modalSeedPhrase');
            const toggleIcon = document.getElementById('seedPhraseToggleIcon');

            if (seedPhraseField.dataset.original && seedPhraseField.value.includes('*')) {
                // Show original seed phrase
                seedPhraseField.value = seedPhraseField.dataset.original;
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                // Hide seed phrase
                hideSeedPhrase();
            }
        }

        // Copy to clipboard
        async function copyToClipboard(text) {
            const toast = document.getElementById('copyToast');
            const toastMessage = document.getElementById('copyToastMessage');

            try {
                await navigator.clipboard.writeText(text);

                // Show toast
                toastMessage.textContent = 'Copied to clipboard!';
                toast.classList.remove('translate-x-full');

                // Hide toast after 3 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                }, 3000);

            } catch (err) {
                console.error('Failed to copy:', err);
                toastMessage.textContent = 'Failed to copy to clipboard';
                toast.classList.remove('translate-x-full');

                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                }, 3000);
            }
        }

        // Close modal when clicking outside
        document.getElementById('walletDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWalletDetailsModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeWalletDetailsModal();
            }
        });
    </script>
@endpush