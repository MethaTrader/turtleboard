@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Add Web3 Wallet</h2>
                <a href="{{ route('accounts.web3') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Step-by-Step Form with Alpine.js -->
            <div class="bg-card rounded-card shadow-card p-6" x-data="web3WalletForm()">
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
                                    <i class="fas" :class="currentStep >= 1 ? 'fa-check' : 'fa-globe'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Select Network</div>
                            </div>
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 2, 'text-text-secondary': currentStep < 2}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 2}">
                                    <i class="fas" :class="currentStep >= 2 ? 'fa-check' : 'fa-wallet'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Wallet Creation</div>
                            </div>
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 3, 'text-text-secondary': currentStep < 3}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 3}">
                                    <i class="fas" :class="currentStep >= 3 ? 'fa-check' : 'fa-save'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Confirmation</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('accounts.web3.store') }}" method="POST" x-ref="mainForm">
                    @csrf

                    <!-- Hidden fields to ensure all data is submitted -->
                    <input type="hidden" name="network" :value="formData.network">
                    <input type="hidden" name="creation_method" :value="formData.creation_method">
                    <input type="hidden" name="address" :value="formData.address">
                    <input type="hidden" name="seed_phrase" :value="formData.seed_phrase">

                    <!-- Step 1: Select Network -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Select Blockchain Network</h3>
                        <p class="text-text-secondary mb-6">Choose the blockchain network for your wallet.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Ethereum Network -->
                            <div class="relative">
                                <input type="radio" id="network_ethereum" value="ethereum"
                                       class="hidden peer" x-model="formData.network">
                                <label for="network_ethereum"
                                       class="block border-2 rounded-lg p-4 cursor-pointer transition-all
                                peer-checked:border-secondary peer-checked:bg-secondary/5
                                hover:border-secondary/50">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-12 h-12 flex-shrink-0 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                                            <i class="fab fa-ethereum text-blue-500 text-xl"></i>
                                        </div>
                                        <div class="text-text-primary font-medium">Ethereum</div>
                                        <div class="text-text-secondary text-xs mt-1">ERC-20 Network</div>
                                    </div>
                                </label>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <i class="fas fa-check-circle text-secondary"></i>
                                </div>
                            </div>

                            <!-- Binance Smart Chain -->
                            <div class="relative">
                                <input type="radio" id="network_binance" value="binance"
                                       class="hidden peer" x-model="formData.network">
                                <label for="network_binance"
                                       class="block border-2 rounded-lg p-4 cursor-pointer transition-all
                                peer-checked:border-secondary peer-checked:bg-secondary/5
                                hover:border-secondary/50">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-12 h-12 flex-shrink-0 rounded-full bg-yellow-100 flex items-center justify-center mb-3">
                                            <i class="fas fa-coins text-yellow-500 text-xl"></i>
                                        </div>
                                        <div class="text-text-primary font-medium">Binance Smart Chain</div>
                                        <div class="text-text-secondary text-xs mt-1">BEP-20 Network</div>
                                    </div>
                                </label>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <i class="fas fa-check-circle text-secondary"></i>
                                </div>
                            </div>

                            <!-- Aptos Network (Disabled) -->
                            <div class="relative opacity-50">
                                <input type="radio" id="network_aptos" value="aptos"
                                       class="hidden peer" disabled>
                                <label for="network_aptos"
                                       class="block border-2 border-dashed rounded-lg p-4 cursor-not-allowed transition-all">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-12 h-12 flex-shrink-0 rounded-full bg-purple-100 flex items-center justify-center mb-3">
                                            <i class="fas fa-network-wired text-purple-500 text-xl"></i>
                                        </div>
                                        <div class="text-text-primary font-medium">Aptos</div>
                                        <div class="text-text-secondary text-xs mt-1">Coming Soon</div>
                                    </div>
                                </label>
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-500 text-xs rounded-full">Disabled</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-red-500 text-sm mt-2" x-show="errors.network" x-text="errors.network"></div>
                    </div>

                    <!-- Step 2: Wallet Creation Method -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Create or Import Wallet</h3>
                        <p class="text-text-secondary mb-6" x-text="`Choose how you want to add your ${formData.network} wallet.`"></p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Generate New Wallet -->
                            <div class="relative">
                                <input type="radio" id="method_generate" value="generate"
                                       class="hidden peer" x-model="formData.creation_method">
                                <label for="method_generate"
                                       class="block h-full border-2 rounded-lg p-4 cursor-pointer transition-all
                                peer-checked:border-secondary peer-checked:bg-secondary/5
                                hover:border-secondary/50">
                                    <div class="flex flex-col items-center text-center h-full">
                                        <div class="w-12 h-12 flex-shrink-0 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                            <i class="fas fa-plus text-green-500 text-xl"></i>
                                        </div>
                                        <div class="text-text-primary font-medium">Generate New Wallet</div>
                                        <div class="text-text-secondary text-xs mt-2">
                                            Create a new wallet with a randomly generated seed phrase and address.
                                        </div>
                                    </div>
                                </label>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <i class="fas fa-check-circle text-secondary"></i>
                                </div>
                            </div>

                            <!-- Import Existing Wallet -->
                            <div class="relative">
                                <input type="radio" id="method_import" value="import"
                                       class="hidden peer" x-model="formData.creation_method">
                                <label for="method_import"
                                       class="block h-full border-2 rounded-lg p-4 cursor-pointer transition-all
                                peer-checked:border-secondary peer-checked:bg-secondary/5
                                hover:border-secondary/50">
                                    <div class="flex flex-col items-center text-center h-full">
                                        <div class="w-12 h-12 flex-shrink-0 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                                            <i class="fas fa-download text-blue-500 text-xl"></i>
                                        </div>
                                        <div class="text-text-primary font-medium">Import Existing Wallet</div>
                                        <div class="text-text-secondary text-xs mt-2">
                                            Import an existing wallet using your address and seed phrase.
                                        </div>
                                    </div>
                                </label>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <i class="fas fa-check-circle text-secondary"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Generate New Wallet Section -->
                        <div x-show="formData.creation_method === 'generate'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-medium text-text-primary">Generate New <span x-text="formData.network.charAt(0).toUpperCase() + formData.network.slice(1)"></span> Wallet</h4>
                                    <button type="button" @click="generateWallet" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button flex items-center">
                                        <i class="fas fa-sync-alt mr-2"></i>
                                        <span x-text="walletGenerated ? 'Regenerate' : 'Generate'"></span>
                                    </button>
                                </div>

                                <div x-show="!walletGenerated" class="text-center py-8 text-text-secondary">
                                    <i class="fas fa-wallet text-4xl mb-2 opacity-50"></i>
                                    <p>Click the "Generate" button to create a new wallet</p>
                                </div>

                                <div x-show="walletGenerated" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-text-secondary mb-1">Wallet Address</label>
                                        <div class="relative">
                                            <input type="text" x-model="formData.address" readonly
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary font-mono">
                                            <button type="button" @click="copyToClipboard(formData.address)"
                                                    class="absolute right-2 top-2 text-gray-400 hover:text-secondary">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-text-secondary mb-1">Seed Phrase</label>
                                        <div class="relative">
                                            <textarea x-model="formData.seed_phrase" readonly
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary font-mono" rows="2"></textarea>
                                            <button type="button" @click="copyToClipboard(formData.seed_phrase)"
                                                    class="absolute right-2 top-2 text-gray-400 hover:text-secondary">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <div class="mt-1 text-xs text-danger">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Important: Store this seed phrase securely. Anyone with this phrase can access your wallet.
                                        </div>
                                    </div>

                                    <div x-show="privateKey" class="border-t border-gray-200 pt-4">
                                        <label class="block text-sm font-medium text-text-secondary mb-1">Private Key</label>
                                        <div class="relative">
                                            <input type="text" x-model="privateKey" readonly
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-text-primary font-mono">
                                            <button type="button" @click="copyToClipboard(privateKey)"
                                                    class="absolute right-2 top-2 text-gray-400 hover:text-secondary">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <div class="mt-1 text-xs text-danger">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Keep your private key secure. It provides full access to your wallet.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Import Existing Wallet Section -->
                        <div x-show="formData.creation_method === 'import'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h4 class="font-medium text-text-primary mb-4">Import Existing <span x-text="formData.network.charAt(0).toUpperCase() + formData.network.slice(1)"></span> Wallet</h4>

                                <div class="space-y-4">
                                    <div>
                                        <label for="import_address" class="block text-sm font-medium text-text-secondary mb-1">Wallet Address</label>
                                        <input type="text" id="import_address" x-model="formData.address"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-text-primary font-mono">
                                        <div class="text-xs text-text-secondary mt-1">
                                            Enter your wallet address (starts with 0x for Ethereum and Binance Smart Chain)
                                        </div>
                                        <div class="text-red-500 text-xs mt-1" x-show="errors.address" x-text="errors.address"></div>
                                    </div>

                                    <div>
                                        <label for="import_seed_phrase" class="block text-sm font-medium text-text-secondary mb-1">Seed Phrase</label>
                                        <textarea id="import_seed_phrase" x-model="formData.seed_phrase"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-text-primary font-mono" rows="2"></textarea>
                                        <div class="text-xs text-text-secondary mt-1">
                                            Enter your 12, 15, 18, 21, or 24-word seed phrase, with words separated by spaces
                                        </div>
                                        <div class="text-red-500 text-xs mt-1" x-show="errors.seed_phrase" x-text="errors.seed_phrase"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-red-500 text-sm mt-2" x-show="errors.creation_method" x-text="errors.creation_method"></div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Confirm Wallet Details</h3>
                        <p class="text-text-secondary mb-6">Please review your wallet information before saving.</p>

                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-text-secondary">Network</h4>
                                    <p class="text-text-primary" x-text="formData.network.charAt(0).toUpperCase() + formData.network.slice(1)"></p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-text-secondary">Creation Method</h4>
                                    <p class="text-text-primary" x-text="formData.creation_method === 'generate' ? 'Generated New Wallet' : 'Imported Existing Wallet'"></p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-text-secondary">Wallet Address</h4>
                                    <div class="flex items-center">
                                        <p class="text-text-primary font-mono truncate" x-text="formData.address"></p>
                                        <button type="button" @click="copyToClipboard(formData.address)"
                                                class="ml-2 text-secondary hover:text-secondary/80">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-text-secondary">Seed Phrase</h4>
                                    <div class="flex items-center">
                                        <p class="text-text-primary font-mono truncate" x-text="'••••••••••••••••••••••••••••'"></p>
                                        <button type="button" @click="toggleSeedPhraseVisibility"
                                                class="ml-2 text-secondary hover:text-secondary/80">
                                            <i class="fas" :class="showSeedPhrase ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    <div x-show="showSeedPhrase" class="mt-2 p-2 bg-gray-100 rounded-md font-mono text-sm">
                                        <p x-text="formData.seed_phrase"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Make sure you have securely saved your seed phrase. If you lose it, you will not be able to recover your wallet.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toast Notification for Copy Actions -->
                    <div id="copyToast" class="fixed top-4 right-0 bg-success text-white px-4 py-2 rounded-md shadow-lg transform translate-x-full transition-transform duration-300 z-50">
                        <i class="fas fa-check mr-2"></i>
                        <span id="copyToastMessage">Copied to clipboard!</span>
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
                                    <i class="fas fa-save mr-2"></i> Save Wallet
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>
    <script>
        function web3WalletForm() {
            return {
                currentStep: 1,
                formData: {
                    network: 'ethereum',
                    creation_method: 'generate',
                    address: '',
                    seed_phrase: '',
                },
                walletGenerated: false,
                privateKey: '',
                errors: {},
                loading: false,
                showSeedPhrase: false,

                get progress() {
                    return ((this.currentStep - 1) / 2) * 100;
                },

                nextStep() {
                    // Clear previous errors
                    this.errors = {};

                    // Validate current step
                    if (this.currentStep === 1) {
                        if (!this.formData.network) {
                            this.errors.network = 'Please select a blockchain network.';
                            return;
                        }
                    } else if (this.currentStep === 2) {
                        if (!this.formData.creation_method) {
                            this.errors.creation_method = 'Please select a wallet creation method.';
                            return;
                        }

                        if (this.formData.creation_method === 'generate') {
                            if (!this.walletGenerated) {
                                // Generate a wallet automatically if the user hasn't done so yet
                                this.generateWallet();
                            }

                            // Additional validation after generating
                            if (!this.formData.address || !this.formData.seed_phrase) {
                                this.errors.creation_method = 'Wallet generation failed. Please try again.';
                                return;
                            }
                        }

                        if (this.formData.creation_method === 'import') {
                            if (!this.formData.address) {
                                this.errors.address = 'Please enter a wallet address.';
                                return;
                            }

                            // Validate Ethereum/BSC address format
                            if (this.formData.network === 'ethereum' || this.formData.network === 'binance') {
                                if (!/^0x[a-fA-F0-9]{40}$/.test(this.formData.address)) {
                                    this.errors.address = `Please enter a valid ${this.formData.network === 'ethereum' ? 'Ethereum' : 'Binance Smart Chain'} address (0x followed by 40 hexadecimal characters).`;
                                    return;
                                }
                            }

                            if (!this.formData.seed_phrase) {
                                this.errors.seed_phrase = 'Please enter a seed phrase.';
                                return;
                            }

                            // Basic validation for seed phrase
                            const words = this.formData.seed_phrase.trim().split(/\s+/);
                            if (![12, 15, 18, 21, 24].includes(words.length)) {
                                this.errors.seed_phrase = 'Seed phrase must contain 12, 15, 18, 21, or 24 words separated by spaces.';
                                return;
                            }
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

                generateWallet() {
                    try {
                        // Generate a new random wallet
                        const wallet = ethers.Wallet.createRandom();

                        // Get seed phrase, private key, and address
                        this.formData.seed_phrase = wallet.mnemonic.phrase;
                        this.formData.address = wallet.address;
                        this.privateKey = wallet.privateKey;

                        // Mark wallet as generated
                        this.walletGenerated = true;

                        console.log("Wallet generated successfully!");
                    } catch (error) {
                        console.error("Error generating wallet:", error);
                        alert("An error occurred while generating the wallet. Please try again.");
                    }
                },

                toggleSeedPhraseVisibility() {
                    this.showSeedPhrase = !this.showSeedPhrase;
                },

                async copyToClipboard(text) {
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
                },

                async finalSubmit() {
                    // Clear previous errors
                    this.errors = {};

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