@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Edit MEXC Account</h2>
                <a href="{{ route('accounts.mexc') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-card rounded-card shadow-card p-6">
                <form action="{{ route('accounts.mexc.update', $mexcAccount) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <div class="bg-gray-50 p-4 rounded-md mb-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full
                                    @if($mexcAccount->emailAccount->provider == 'Gmail') bg-red-100 text-red-500
                                    @elseif($mexcAccount->emailAccount->provider == 'Outlook') bg-blue-100 text-blue-500
                                    @elseif($mexcAccount->emailAccount->provider == 'Yahoo') bg-purple-100 text-purple-500
                                    @else bg-amber-100 text-amber-500
                                    @endif flex items-center justify-center mr-4">
                                        <i class="
                                        @if($mexcAccount->emailAccount->provider == 'Gmail') fab fa-google
                                        @elseif($mexcAccount->emailAccount->provider == 'Outlook') fab fa-microsoft
                                        @elseif($mexcAccount->emailAccount->provider == 'Yahoo') fab fa-yahoo
                                        @else fas fa-envelope
                                        @endif"></i>
                                    </div>
                                    <div>
                                        <div class="text-text-primary font-medium">{{ $mexcAccount->emailAccount->email_address }}</div>
                                        <div class="text-text-secondary text-xs">{{ $mexcAccount->emailAccount->provider }}</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="email_account_id" value="{{ $mexcAccount->email_account_id }}">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-text-secondary mb-1">Password (leave blank to keep current)</label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                       class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                       placeholder="Enter new password">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center space-x-2">
                                    <button type="button" onclick="generatePassword()" class="text-gray-400 hover:text-secondary" title="Generate password">
                                        <i class="fas fa-dice"></i>
                                    </button>
                                    <div class="cursor-pointer text-gray-400 hover:text-secondary" onclick="togglePasswordVisibility()" title="Toggle password visibility">
                                        <i class="fas fa-eye" id="togglePassword"></i>
                                    </div>
                                </div>
                            </div>
                            @error('password')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                            <select id="status" name="status" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="active" {{ $mexcAccount->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $mexcAccount->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ $mexcAccount->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="web3_wallet_id" class="block text-sm font-medium text-text-secondary mb-1">Web3 Wallet (Optional)</label>
                            <select id="web3_wallet_id" name="web3_wallet_id" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">No Wallet</option>
                                @foreach($availableWeb3Wallets as $wallet)
                                    <option value="{{ $wallet->id }}" {{ $mexcAccount->web3_wallet_id == $wallet->id ? 'selected' : '' }}>
                                        {{ $wallet->getFormattedAddress() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('web3_wallet_id')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('accounts.mexc') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-save mr-2"></i> Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function generatePassword() {
            // Define possible character sets
            const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // No I or O (easily confused with 1 and 0)
            const lowercase = 'abcdefghijkmnopqrstuvwxyz'; // No l (easily confused with 1)
            const numbers = '23456789'; // No 0 or 1 (easily confused with O and l)
            const symbols = '!@#$%^&*_-+=';

            // Ensure at least one of each type of character
            let password = '';
            password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
            password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
            password += numbers.charAt(Math.floor(Math.random() * numbers.length));
            password += symbols.charAt(Math.floor(Math.random() * symbols.length));

            // Add more random characters to reach minimum length of 12
            const allChars = uppercase + lowercase + numbers + symbols;
            for (let i = password.length; i < 12; i++) {
                password += allChars.charAt(Math.floor(Math.random() * allChars.length));
            }

            // Shuffle the password to avoid patterns
            password = password.split('').sort(() => 0.5 - Math.random()).join('');

            // Set password field value
            document.getElementById('password').value = password;

            // Show the password
            document.getElementById('password').type = 'text';
            document.getElementById('togglePassword').classList.remove('fa-eye');
            document.getElementById('togglePassword').classList.add('fa-eye-slash');
        }
    </script>
@endpush