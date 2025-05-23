<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p>Welcome, {{ Auth::user()->name }}!</p>
                    <p class="mt-2">Your role: <span class="font-semibold">{{ ucfirst(Auth::user()->role) }}</span></p>

                    @if (Auth::user()->isAdmin())
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold">Admin Quick Links</h3>
                            <ul class="mt-2 list-disc list-inside">
                                <li><a href="{{ route('admin.users.index') }}" class="text-blue-500 hover:underline">Manage Users</a></li>
                                <!-- Add other admin links here -->
                            </ul>
                        </div>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->isAccountManager())
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold">Account Management</h3>
                            <ul class="mt-2 list-disc list-inside">
                                <li><a href="{{ route('accounts.index') }}" class="text-blue-500 hover:underline">All Accounts</a></li>
                                <li><a href="#" class="text-blue-500 hover:underline">MEXC Accounts</a></li>
                                <li><a href="#" class="text-blue-500 hover:underline">Email Accounts</a></li>
                                <li><a href="#" class="text-blue-500 hover:underline">Proxies</a></li>
                                <li><a href="#" class="text-blue-500 hover:underline">Web3 Wallets</a></li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>