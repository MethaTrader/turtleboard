@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Edit Web3 Wallet</h2>
                <a href="{{ route('accounts.web3') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-card rounded-card shadow-card p-6">
                <form action="{{ route('accounts.web3.update', $wallet) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <div class="bg-gray-50 p-4 rounded-md mb-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full flex items-center justify-center mr-4
                                        @if($wallet->network === 'ethereum')
                                            bg-blue-100
                                        @elseif($wallet->network === 'binance')
                                            bg-yellow-100
                                        @else
                                            bg-purple-100
                                        @endif">
                                        @if($wallet->network === 'ethereum')
                                            <i class="fab fa-ethereum text-blue-500 text-2xl"></i>
                                        @elseif($wallet->network === 'binance')
                                            <i class="fas fa-coins text-yellow-600 text-2xl"></i>
                                        @else
                                            <i class="fas fa-network-wired text-purple-500 text-2xl"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-text-primary font-medium">
                                            {{ ucfirst($wallet->network) }}
                                            @if($wallet->network === 'binance')
                                                Smart Chain
                                            @endif
                                            Network
                                        </div>
                                        <div class="text-text-secondary text-xs">Created {{ $wallet->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-text-secondary mb-1">Wallet Address</label>
                            <input type="text" id="address" name="address" value="{{ old('address', $wallet->address) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20 font-mono bg-gray-50" readonly>
                            @error('address')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="seed_phrase" class="block text-sm font-medium text-text-secondary mb-1">Seed Phrase</label>
                            <div class="relative">
                                <input type="password" id="seed_phrase" name="seed_phrase" value="{{ old('seed_phrase', $wallet->seed_phrase) }}"
                                       class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20 font-mono">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <div class="cursor-pointer text-gray-400 hover:text-secondary" onclick="toggleSeedPhraseVisibility()" title="Toggle visibility">
                                        <i class="fas fa-eye" id="toggleSeedPhrase"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-text-secondary mt-1">
                                Leave blank to keep the current seed phrase. Only update if necessary.
                            </div>
                            @error('seed_phrase')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="network" value="{{ $wallet->network }}">
                        <input type="hidden" name="creation_method" value="import">
                    </div>

                    @if($wallet->mexcAccount)
                        <div class="mb-6">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Note:</strong> This wallet is currently connected to MEXC account: <strong>{{ $wallet->mexcAccount->emailAccount->email_address }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('accounts.web3') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-save mr-2"></i> Update Wallet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleSeedPhraseVisibility() {
            const seedPhraseInput = document.getElementById('seed_phrase');
            const toggleIcon = document.getElementById('toggleSeedPhrase');

            if (seedPhraseInput.type === 'password') {
                seedPhraseInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                seedPhraseInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
@endpush