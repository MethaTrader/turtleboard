@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Add MEXC Account</h2>
                <a href="{{ route('accounts.mexc') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Step-by-Step Form with Alpine.js -->
            <div class="bg-card rounded-card shadow-card p-6" x-data="mexcAccountForm()">
                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="relative pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-text-secondary text-xs">Step <span x-text="currentStep"></span> of 3</div>
                            <div class="text-text-secondary text-xs"><span x-text="Math.round(progress)"></span>% Complete</div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-gray-200">
                            <div :style="`width: ${progress}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-secondary transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between">
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 1, 'text-text-secondary': currentStep < 1}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 1}">
                                    <i class="fas" :class="currentStep >= 1 ? 'fa-check' : 'fa-envelope'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Email Account</div>
                            </div>
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 2, 'text-text-secondary': currentStep < 2}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 2}">
                                    <i class="fas" :class="currentStep >= 2 ? 'fa-check' : 'fa-key'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Password</div>
                            </div>
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 3, 'text-text-secondary': currentStep < 3}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 3}">
                                    <i class="fas" :class="currentStep >= 3 ? 'fa-check' : 'fa-wallet'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Web3 Wallet</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('accounts.mexc.store') }}" method="POST" x-ref="mainForm">
                    @csrf

                    <!-- Hidden fields to ensure all data is submitted -->
                    <input type="hidden" name="email_account_id" :value="formData.email_account_id">
                    <input type="hidden" name="password" :value="formData.password">
                    <input type="hidden" name="web3_wallet_id" :value="formData.web3_wallet_id">
                    <input type="hidden" name="status" :value="formData.status">

                    <!-- Step 1: Select Email Account -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Select Email Account</h3>
                        <p class="text-text-secondary mb-6">Choose an email account to link with your MEXC account.</p>

                        @if($availableEmailAccounts->isEmpty())
                            <div class="bg-warning/10 text-warning p-4 border-l-4 border-warning rounded-md mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">
                                            No available email accounts found. Please <a href="{{ route('accounts.email.create') }}" class="font-medium underline">create an email account</a> first.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-4 mb-6">
                                @foreach($availableEmailAccounts as $emailAccount)
                                    <div class="relative">
                                        <input type="radio" id="email_{{ $emailAccount->id }}" value="{{ $emailAccount->id }}"
                                               class="hidden peer" x-model="formData.email_account_id">
                                        <label for="email_{{ $emailAccount->id }}"
                                               class="block border-2 rounded-lg p-4 cursor-pointer transition-all
                                        peer-checked:border-secondary peer-checked:bg-secondary/5
                                        hover:border-secondary/50">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center
                                                @if($emailAccount->provider == 'Gmail') bg-red-100 text-red-500
                                                @elseif($emailAccount->provider == 'Outlook') bg-blue-100 text-blue-500
                                                @elseif($emailAccount->provider == 'Yahoo') bg-purple-100 text-purple-500
                                                @else bg-amber-100 text-amber-500
                                                @endif">
                                                    <i class="
                                                    @if($emailAccount->provider == 'Gmail') fab fa-google
                                                    @elseif($emailAccount->provider == 'Outlook') fab fa-microsoft
                                                    @elseif($emailAccount->provider == 'Yahoo') fab fa-yahoo
                                                    @else fas fa-envelope
                                                    @endif"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-text-primary font-medium">{{ $emailAccount->email_address }}</div>
                                                    <div class="text-text-secondary text-xs flex">
                                                        <span class="mr-2">{{ $emailAccount->provider }}</span>
                                                        <span class="px-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-success/10 text-success">Active</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <i class="fas fa-check-circle text-secondary"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="text-red-500 text-sm mt-2" x-show="errors.email_account_id" x-text="errors.email_account_id"></div>
                    </div>

                    <!-- Step 2: Enter Password -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Set Account Password</h3>
                        <p class="text-text-secondary mb-6">Create a secure password for your MEXC account.</p>

                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-text-secondary mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="password" x-model="formData.password"
                                       class="block w-full pl-10 pr-20 rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                       placeholder="Enter your password">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" @click="generatePassword"
                                            class="text-gray-400 hover:text-secondary mr-2"
                                            title="Generate password">
                                        <i class="fas fa-dice"></i>
                                    </button>
                                    <button type="button" @click="togglePassword"
                                            class="text-gray-400 hover:text-secondary"
                                            title="Toggle password visibility">
                                        <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-red-500 text-sm mt-2" x-show="errors.password" x-text="errors.password"></div>
                        </div>

                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Account Status</label>
                            <select id="status" x-model="formData.status"
                                    class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <div class="text-text-secondary text-xs mt-1">
                                Set the initial status for this MEXC account.
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Web3 Wallet (Optional) -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Connect Web3 Wallet (Optional)</h3>
                        <p class="text-text-secondary mb-6">Link a Web3 wallet to your MEXC account (optional).</p>

                        @if($availableWeb3Wallets->isEmpty())
                            <div class="bg-blue-50 text-blue-700 p-4 border-l-4 border-blue-400 rounded-md mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">
                                            No available Web3 wallets found. You can <a href="{{ route('accounts.web3.create') }}" class="font-medium underline">create a Web3 wallet</a> or continue without linking one.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-6">
                                <div class="flex items-center mb-4">
                                    <input id="no_wallet" name="wallet_option" type="radio" value="none" x-model="walletOption" class="w-4 h-4 text-secondary focus:ring-secondary/20 border-gray-300">
                                    <label for="no_wallet" class="ml-2 text-sm font-medium text-text-primary">Do not connect a wallet</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="connect_wallet" name="wallet_option" type="radio" value="connect" x-model="walletOption" class="w-4 h-4 text-secondary focus:ring-secondary/20 border-gray-300">
                                    <label for="connect_wallet" class="ml-2 text-sm font-medium text-text-primary">Connect an existing wallet</label>
                                </div>
                            </div>

                            <div x-show="walletOption === 'connect'" class="mt-4">
                                <label for="web3_wallet_id" class="block text-sm font-medium text-text-secondary mb-1">Select Wallet</label>
                                <select id="web3_wallet_id" x-model="formData.web3_wallet_id"
                                        class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                    <option value="">-- Select a wallet --</option>
                                    @foreach($availableWeb3Wallets as $wallet)
                                        <option value="{{ $wallet->id }}">{{ $wallet->getFormattedAddress() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="bg-gray-50 p-4 rounded-md mt-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-text-secondary"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-text-secondary">
                                        You can connect a Web3 wallet later by editing this account.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button
                                type="button"
                                @click="prevStep"
                                x-show="currentStep > 1"
                                class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button focus:outline-none">
                            <i class="fas fa-arrow-left mr-2"></i> Previous
                        </button>

                        <div class="ml-auto">
                            <button
                                    type="button"
                                    @click="nextStep"
                                    x-show="currentStep < 3"
                                    class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button focus:outline-none">
                                Next <i class="fas fa-arrow-right ml-2"></i>
                            </button>

                            <button
                                    type="button"
                                    @click="finalSubmit"
                                    x-show="currentStep === 3"
                                    class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button focus:outline-none"
                                    :disabled="loading">
                                <span x-show="!loading">
                                    <i class="fas fa-save mr-2"></i> Save Account
                                </span>
                                <span x-show="loading">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function mexcAccountForm() {
            return {
                currentStep: 1,
                formData: {
                    email_account_id: '',
                    password: '',
                    web3_wallet_id: '',
                    status: 'active',
                },
                walletOption: 'none',
                errors: {},
                loading: false,
                showPassword: false,

                get progress() {
                    return ((this.currentStep - 1) / 2) * 100;
                },

                nextStep() {
                    // Clear previous errors
                    this.errors = {};

                    // Validate current step
                    if (this.currentStep === 1) {
                        if (!this.formData.email_account_id) {
                            this.errors.email_account_id = 'Please select an email account.';
                            return;
                        }
                    } else if (this.currentStep === 2) {
                        if (!this.formData.password) {
                            this.errors.password = 'Please enter a password.';
                            return;
                        }
                        if (this.formData.password.length < 6) {
                            this.errors.password = 'Password must be at least 6 characters.';
                            return;
                        }
                    }

                    // Move to next step
                    if (this.currentStep < 3) {
                        this.currentStep++;
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        // Clear errors when going back
                        this.errors = {};
                    }
                },

                togglePassword() {
                    this.showPassword = !this.showPassword;
                    const passwordInput = document.getElementById('password');
                    if (passwordInput) {
                        passwordInput.type = this.showPassword ? 'text' : 'password';
                    }
                },

                generatePassword() {
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

                    this.formData.password = password;

                    // Show the password
                    this.showPassword = true;
                    const passwordInput = document.getElementById('password');
                    if (passwordInput) {
                        passwordInput.type = 'text';
                    }
                },

                async finalSubmit() {
                    // Clear previous errors
                    this.errors = {};

                    // Validate wallet selection if the connect option is chosen
                    if (this.walletOption === 'connect' && !this.formData.web3_wallet_id) {
                        this.errors.web3_wallet_id = 'Please select a wallet or choose "Do not connect a wallet".';
                        return;
                    }

                    // If "Do not connect a wallet" is chosen, make sure web3_wallet_id is empty
                    if (this.walletOption === 'none') {
                        this.formData.web3_wallet_id = '';
                    }

                    this.loading = true;

                    try {
                        // Submit the form
                        this.$refs.mainForm.submit();
                    } catch (error) {
                        console.error('Submission error:', error);
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endpush