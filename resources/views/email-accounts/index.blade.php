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
                <div class="bg-success/10 text-success p-4 border-l-4 border-success mb-4">
                    {{ session('success') }}
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
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-text-primary">{{ $account->provider }}</div>
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
                                    <a href="{{ route('accounts.email.edit', $account) }}" class="text-secondary hover:text-secondary/80 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('accounts.email.destroy', $account) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-danger hover:text-danger/80" onclick="return confirm('Are you sure you want to delete this email account?')">
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
@endsection