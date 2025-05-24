<!-- resources/views/email-accounts/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-text-primary">Email Accounts</h2>
            <a href="{{ route('accounts.email.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button flex items-center">
                <i class="fas fa-plus mr-2"></i>
                <span>Add Email Account</span>
            </a>
        </div>

        <!-- Filters Section -->
        <div class="bg-card p-4 rounded-card shadow-card">
            <form action="{{ route('accounts.email') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="provider" class="block text-sm font-medium text-text-secondary mb-1">Provider</label>
                    <select id="provider" name="provider" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="">All Providers</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider }}" {{ request('provider') == $provider ? 'selected' : '' }}>{{ $provider }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-text-secondary mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by email address" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                    <a href="{{ route('accounts.email') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Email Accounts Table -->
        <div class="bg-card rounded-card shadow-card overflow-hidden">
            @if(session('success'))
                <div class="bg-success/10 text-success p-4 border-l-4 border-success mb-4 flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    @if(session('created_account_id'))
                        <button onclick="viewAccountCredentials({{ session('created_account_id') }})"
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

            @if($emailAccounts->isEmpty())
                <div class="p-6 text-center">
                    <div class="text-5xl text-gray-300 mb-4">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">No Email Accounts Found</h3>
                    <p class="text-text-secondary mb-4">You haven't added any email accounts yet, or none match your filters.</p>
                    <a href="{{ route('accounts.email.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                        <i class="fas fa-plus mr-2"></i> Add Your First Email Account
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Email Address
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Provider
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                Proxy
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
                        @foreach($emailAccounts as $account)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-secondary/10 flex items-center justify-center">
                                            <i class="fas fa-envelope text-secondary"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-text-primary">{{ $account->email_address }}</div>
                                            @if($account->first_name || $account->last_name)
                                                <div class="text-xs text-text-secondary">{{ $account->full_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @if($account->provider == 'Gmail') bg-red-100 text-red-500
                                    @elseif($account->provider == 'Outlook') bg-blue-100 text-blue-500
                                    @elseif($account->provider == 'Yahoo') bg-purple-100 text-purple-500
                                    @else bg-amber-100 text-amber-500
                                    @endif">
                                        <i class="
                                        @if($account->provider == 'Gmail') fab fa-google
                                        @elseif($account->provider == 'Outlook') fab fa-microsoft
                                        @elseif($account->provider == 'Yahoo') fab fa-yahoo
                                        @else fas fa-envelope
                                        @endif text-lg"></i>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($account->status === 'active')
                                            bg-success/10 text-success
                                        @elseif($account->status === 'inactive')
                                            bg-warning/10 text-warning
                                        @else
                                            bg-danger/10 text-danger
                                        @endif">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($account->proxy)
                                        <div class="text-sm text-text-primary">{{ $account->proxy->ip_address }}:{{ $account->proxy->port }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">No proxy</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                    {{ $account->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="viewAccountCredentials({{ $account->id }})"
                                            class="text-primary hover:text-primary/80 mr-3"
                                            title="View Credentials">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('accounts.email.edit', $account) }}" class="text-secondary hover:text-secondary/80 mr-3" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('accounts.email.destroy', $account) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-danger hover:text-danger/80" title="Delete" onclick="return confirm('Are you sure you want to delete this email account?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4">
                    {{ $emailAccounts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Account Credentials Modal -->
    <div id="credentialsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-text-primary">Account Credentials</h3>
                    <button onclick="closeCredentialsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-secondary text-2xl mb-2"></i>
                    <p class="text-text-secondary">Loading credentials...</p>
                </div>

                <!-- Modal Content -->
                <div id="modalContent" class="hidden">
                    <!-- Name Fields -->
                    <div class="mb-4" id="nameFieldsContainer">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Name</label>
                        <div id="nameFields" class="text-text-primary font-medium py-2">
                            <!-- Will be filled dynamically -->
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Email Address</label>
                        <div class="relative">
                            <input type="text" id="modalEmail" disabled readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary">
                            <button onclick="copyToClipboard('modalEmail')"
                                    class="absolute right-2 top-2 text-gray-400 hover:text-secondary">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="modalPassword" disabled readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary pr-20">
                            <div class="absolute right-2 top-2 flex space-x-1">
                                <button onclick="togglePasswordVisibility('modalPassword')"
                                        class="text-gray-400 hover:text-secondary">
                                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                </button>
                                <button onclick="copyToClipboard('modalPassword')"
                                        class="text-gray-400 hover:text-secondary">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Provider Info -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Provider</label>
                        <div class="flex items-center">
                            <div id="providerIcon" class="w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                <i class="fab"></i>
                            </div>
                            <span id="modalProvider" class="text-text-primary font-medium"></span>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <div class="flex justify-end">
                        <button onclick="closeCredentialsModal()"
                                class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-times mr-2"></i> Close
                        </button>
                    </div>
                </div>

                <!-- Error State -->
                <div id="modalError" class="hidden text-center py-8">
                    <i class="fas fa-exclamation-triangle text-danger text-2xl mb-2"></i>
                    <p class="text-danger">Failed to load credentials. Please try again.</p>
                    <button onclick="closeCredentialsModal()"
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
        // View account credentials
        async function viewAccountCredentials(accountId) {
            const modal = document.getElementById('credentialsModal');
            const loading = document.getElementById('modalLoading');
            const content = document.getElementById('modalContent');
            const error = document.getElementById('modalError');
            const nameFieldsContainer = document.getElementById('nameFieldsContainer');

            // Show modal and loading state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            error.classList.add('hidden');

            try {
                const response = await fetch(`/accounts/email/${accountId}/credentials`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch credentials');
                }

                const data = await response.json();

                // Populate modal with data
                document.getElementById('modalEmail').value = data.email_address;
                document.getElementById('modalPassword').value = data.password;
                document.getElementById('modalProvider').textContent = data.provider;

                // Handle name fields
                const nameFields = document.getElementById('nameFields');
                if (data.first_name || data.last_name) {
                    nameFields.textContent = `${data.first_name || ''} ${data.last_name || ''}`.trim();
                    nameFieldsContainer.classList.remove('hidden');
                } else {
                    nameFieldsContainer.classList.add('hidden');
                }

                // Set provider icon
                const providerIcon = document.getElementById('providerIcon');
                const iconElement = providerIcon.querySelector('i');

                // Reset classes
                providerIcon.className = 'w-8 h-8 rounded-full flex items-center justify-center mr-3';
                iconElement.className = 'fab';

                // Set provider-specific styling
                switch(data.provider) {
                    case 'Gmail':
                        providerIcon.classList.add('bg-red-100');
                        iconElement.classList.add('fa-google', 'text-red-500');
                        break;
                    case 'Outlook':
                        providerIcon.classList.add('bg-blue-100');
                        iconElement.classList.add('fa-microsoft', 'text-blue-500');
                        break;
                    case 'Yahoo':
                        providerIcon.classList.add('bg-purple-100');
                        iconElement.classList.add('fa-yahoo', 'text-purple-500');
                        break;
                    default:
                        providerIcon.classList.add('bg-amber-100');
                        iconElement.className = 'fas fa-envelope text-amber-500';
                }

                // Show content
                loading.classList.add('hidden');
                content.classList.remove('hidden');

            } catch (err) {
                console.error('Error fetching credentials:', err);
                loading.classList.add('hidden');
                error.classList.remove('hidden');
            }
        }

        // Close credentials modal
        function closeCredentialsModal() {
            const modal = document.getElementById('credentialsModal');
            modal.classList.add('hidden');

            // Reset password field type
            const passwordField = document.getElementById('modalPassword');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }

        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('passwordToggleIcon');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Copy to clipboard
        async function copyToClipboard(fieldId) {
            const field = document.getElementById(fieldId);
            const toast = document.getElementById('copyToast');
            const toastMessage = document.getElementById('copyToastMessage');

            try {
                await navigator.clipboard.writeText(field.value);

                // Show toast
                const fieldType = fieldId.includes('Email') ? 'Email' : 'Password';
                toastMessage.textContent = `${fieldType} copied to clipboard!`;
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
        document.getElementById('credentialsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCredentialsModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCredentialsModal();
            }
        });
    </script>
@endpush