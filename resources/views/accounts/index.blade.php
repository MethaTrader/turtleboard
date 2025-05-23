<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Account Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">MEXC Accounts ({{ $mexcAccounts->count() }})</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-700 border dark:border-gray-600">
                                <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">ID</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Email Account</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Web3 Wallet</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Status</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Created By</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($mexcAccounts as $account)
                                    <tr>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->id }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->emailAccount?->email_address ?? 'None' }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->web3Wallet?->getFormattedAddress() ?? 'None' }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">
                                                <span class="px-2 py-1 rounded text-xs
                                                    @if($account->status === 'active')
                                                        bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($account->status === 'inactive')
                                                        bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                    @else
                                                        bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @endif
                                                ">
                                                    {{ ucfirst($account->status) }}
                                                </span>
                                        </td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->user->name }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Email Accounts ({{ $emailAccounts->count() }})</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-700 border dark:border-gray-600">
                                <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">ID</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Email</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Provider</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Proxy</th>
                                    <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($emailAccounts as $account)
                                    <tr>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->id }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->email_address }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->provider }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">{{ $account->proxy ? $account->proxy->ip_address . ':' . $account->proxy->port : 'None' }}</td>
                                        <td class="py-2 px-4 border-b dark:border-gray-600">
                                                <span class="px-2 py-1 rounded text-xs
                                                    @if($account->status === 'active')
                                                        bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($account->status === 'inactive')
                                                        bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                    @else
                                                        bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @endif
                                                ">
                                                    {{ ucfirst($account->status) }}
                                                </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- You can add sections for Proxies and Web3 Wallets similarly -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>