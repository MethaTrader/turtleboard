<!-- resources/views/email-accounts/create.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Add Email Account</h2>
                <a href="{{ route('accounts.email') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Step-by-Step Form with Alpine.js -->
            <div class="bg-card rounded-card shadow-card p-6" x-data="emailAccountForm()">
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
                                <div class="text-xs">Provider</div>
                            </div>
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 2, 'text-text-secondary': currentStep < 2}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 2}">
                                    <i class="fas" :class="currentStep >= 2 ? 'fa-check' : 'fa-at'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Email Address</div>
                            </div>
                            <div class="w-1/3 text-center" :class="{'text-secondary font-medium': currentStep >= 3, 'text-text-secondary': currentStep < 3}">
                                <div class="mb-1">
                                <span class="w-8 h-8 rounded-full bg-gray-200 inline-flex items-center justify-center" :class="{'bg-secondary text-white': currentStep >= 3}">
                                    <i class="fas" :class="currentStep >= 3 ? 'fa-check' : 'fa-lock'"></i>
                                </span>
                                </div>
                                <div class="text-xs">Password</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('accounts.email.store') }}" method="POST" x-ref="mainForm">
                    @csrf

                    <!-- Hidden fields to ensure all data is submitted -->
                    <input type="hidden" name="provider" :value="formData.provider">
                    <input type="hidden" name="email_address" :value="formData.email_address">
                    <input type="hidden" name="password" :value="formData.password">
                    <input type="hidden" name="proxy_id" :value="formData.proxy_id || ''">
                    <input type="hidden" name="status" :value="formData.status">
                    <input type="hidden" name="first_name" :value="formData.first_name">
                    <input type="hidden" name="last_name" :value="formData.last_name">

                    <!-- Step 1: Select Provider -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Select Email Provider</h3>
                        <p class="text-text-secondary mb-6">Choose the email service provider for your account.</p>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            @foreach($providers as $provider)
                                <div class="relative">
                                    <input type="radio" id="provider_{{ $provider }}" value="{{ $provider }}"
                                           class="hidden peer" x-model="formData.provider">
                                    <label for="provider_{{ $provider }}"
                                           class="block border-2 rounded-lg p-4 cursor-pointer transition-all
                peer-checked:border-secondary peer-checked:bg-secondary/5
                hover:border-secondary/50">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center
                @if($provider == 'Gmail') bg-red-100 text-red-500
                @elseif($provider == 'Outlook') bg-blue-100 text-blue-500
                @elseif($provider == 'Yahoo') bg-purple-100 text-purple-500
                @else bg-amber-100 text-amber-500
                @endif">
                                                <i class="fab
                    @if($provider == 'Gmail') fa-google
                    @elseif($provider == 'Outlook') fa-microsoft
                    @elseif($provider == 'Yahoo') fa-yahoo
                    @elseif($provider == 'iCloud') fa-brands fa-apple
                    @else fa-envelope
                    @endif"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="text-text-primary font-medium">{{ $provider }}</div>
                                                <div class="text-text-secondary text-xs">
                                                    @if($provider == 'Gmail')
                                                        Google Mail Service
                                                    @elseif($provider == 'Outlook')
                                                        Microsoft Email
                                                        <div class="mt-1 flex items-center text-blue-600">
                                                            <i class="fas fa-info-circle mr-1 text-xs"></i>
                                                            <span class="font-medium">Recommended: @outlook.com</span>
                                                        </div>
                                                    @elseif($provider == 'Yahoo')
                                                        Yahoo Mail
                                                    @else
                                                        iCloud Mail
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Enhanced info for Outlook -->
                                    @if($provider == 'Outlook')
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <div class="flex items-center space-x-1">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">
                        @outlook.com preferred
                    </span>
                                                <i class="fas fa-check-circle text-secondary"></i>
                                            </div>
                                        </div>
                                    @else
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <i class="fas fa-check-circle text-secondary"></i>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="text-red-500 text-sm mt-2" x-show="errors.provider" x-text="errors.provider"></div>
                    </div>

                    <!-- Step 2: Enter Email Address -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Enter Email Address</h3>
                        <p class="text-text-secondary mb-6">Please provide the email address for your <span x-text="formData.provider"></span> account.</p>

                        <div class="mb-6">
                            <label for="email_address" class="block text-sm font-medium text-text-secondary mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="email_address" x-model="formData.email_address"
                                       class="block w-full pl-10 rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                       :placeholder="'example@' + (formData.provider ? formData.provider.toLowerCase() : 'provider') + '.com'">
                            </div>

                            <!-- Add this helpful note for Outlook -->
                            <div x-show="formData.provider === 'Outlook'" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                <div class="flex items-center text-blue-700 text-sm">
                                    <i class="fas fa-lightbulb mr-2 text-blue-500"></i>
                                    <span>For better deliverability, we recommend using <strong>@outlook.com</strong> addresses over @hotmail.com</span>
                                </div>
                            </div>

                            <div class="text-red-500 text-sm mt-2" x-show="errors.email_address" x-text="errors.email_address"></div>

                            <!-- Email Suggestions Section (in Step 2) -->
                            <div class="mt-3" x-show="emailSuggestions.length > 0">
                                <label class="block text-sm font-medium text-text-secondary mb-1">Suggestions</label>
                                <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                    <p class="text-xs text-text-secondary mb-2">Click on an option to select it:</p>
                                    <div class="space-y-2">
                                        <template x-for="(suggestion, index) in emailSuggestions" :key="index">
                                            <div @click="selectEmailSuggestion(suggestion)"
                                                 class="p-3 rounded-md border border-gray-200 hover:bg-secondary/5 hover:border-secondary cursor-pointer transition-all relative"
                                                 :class="suggestion.meta.is_best_choice ? 'border-primary bg-primary/5' : ''">

                                                <!-- Best Choice Badge -->
                                                <div x-show="suggestion.meta.is_best_choice"
                                                     class="absolute top-2 right-3 flex items-center">
            <span class="bg-primary text-white text-xs px-2 py-1 rounded-full font-medium flex items-center">
                <span>Best choice</span>
                <span class="ml-1">✨</span>
            </span>
                                                </div>

                                                <!-- Email and Name with domain indicator -->
                                                <div class="text-sm font-medium text-text-primary mb-1 flex items-center justify-between">
                                                    <span x-text="suggestion.email"></span>

                                                    <!-- Add domain indicator for Outlook emails -->
                                                    <span x-show="formData.provider === 'Outlook' && suggestion.email.includes('@outlook.com')"
                                                          class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                Recommended
            </span>
                                                </div>

                                                <!-- Rest remains the same -->
                                                <div class="text-xs text-text-secondary flex items-center justify-between">
                                                    <span x-text="suggestion.meta.first_name + ' ' + suggestion.meta.last_name"></span>
                                                    <!-- Score indicator remains the same -->
                                                </div>

                                                <!-- Uniqueness indicator remains the same -->
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div class="mt-3" x-show="loadingEmailSuggestions">
                                <div class="flex items-center text-text-secondary text-sm">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    <span>Loading suggestions...</span>
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <div class="mt-3">
                                <button type="button" @click="generateEmailSuggestions" class="text-secondary text-sm hover:underline flex items-center">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    <span x-show="formData.provider !== 'Outlook'">Generate email suggestions</span>
                                    <span x-show="formData.provider === 'Outlook'">Generate @outlook.com suggestions</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Enter Password -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-4">
                        <h3 class="text-lg font-semibold mb-4">Enter Password</h3>
                        <p class="text-text-secondary mb-6">Please provide the password for <span x-text="formData.email_address"></span>.</p>

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
                            <label class="block text-sm font-medium text-text-secondary mb-3">Proxy (Optional)</label>

                            @if($availableProxies->count() > 0)
                                <!-- Available Proxies Count -->
                                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                    <div class="flex items-center text-green-700 text-sm">
                                        <i class="fas fa-check-circle mr-2 text-green-500"></i>
                                        <span><strong>{{ $availableProxies->count() }}</strong> available proxies ready to use</span>
                                    </div>
                                </div>

                                <!-- Proxy Selection Options -->
                                <div class="space-y-3">
                                    <!-- No Proxy Option -->
                                    <div class="relative">
                                        <input type="radio" id="no_proxy" name="proxy_option" value="" x-model="formData.proxy_id" class="sr-only">
                                        <label for="no_proxy"
                                               class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all duration-200
                              hover:border-secondary/50 hover:bg-gray-50"
                                               :class="formData.proxy_id === '' ? 'border-secondary bg-secondary/5 ring-2 ring-secondary/20' : 'border-gray-200'">
                                            <div class="flex items-center flex-1">
                                                <!-- Icon -->
                                                <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                                                     :class="formData.proxy_id === '' ? 'bg-secondary/20' : 'bg-gray-100'">
                                                    <i class="fas fa-times text-lg"
                                                       :class="formData.proxy_id === '' ? 'text-secondary' : 'text-gray-500'"></i>
                                                </div>

                                                <!-- Content -->
                                                <div>
                                                    <div class="font-medium text-text-primary">No Proxy</div>
                                                    <div class="text-sm text-text-secondary">Direct connection without proxy</div>
                                                </div>
                                            </div>

                                            <!-- Selection Indicator -->
                                            <div class="ml-auto flex items-center">
                                                <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center transition-all duration-200"
                                                     :class="formData.proxy_id === '' ? 'border-secondary bg-secondary' : 'border-gray-300'">
                                                    <div class="w-2 h-2 bg-white rounded-full transition-opacity duration-200"
                                                         :class="formData.proxy_id === '' ? 'opacity-100' : 'opacity-0'"></div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Available Proxies -->
                                    <div class="max-h-64 overflow-y-auto space-y-3">
                                        @foreach($availableProxies as $proxy)
                                            <div class="relative">
                                                <input type="radio" id="proxy_{{ $proxy->id }}" name="proxy_option" value="{{ $proxy->id }}" x-model="formData.proxy_id" class="sr-only">
                                                <label for="proxy_{{ $proxy->id }}"
                                                       class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all duration-200
                                      hover:border-secondary/50 hover:bg-gray-50"
                                                       :class="formData.proxy_id == '{{ $proxy->id }}' ? 'border-secondary bg-secondary/5 ring-2 ring-secondary/20' : 'border-gray-200'">

                                                    <div class="flex items-center flex-1">
                                                        <!-- Proxy Icon and Country Flag -->
                                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 relative"
                                                             :class="formData.proxy_id == '{{ $proxy->id }}' ? 'bg-secondary/20' : 'bg-secondary/10'">
                                                            <i class="fas fa-server text-lg"
                                                               :class="formData.proxy_id == '{{ $proxy->id }}' ? 'text-secondary' : 'text-secondary/70'"></i>
                                                            @if($proxy->country_code)
                                                                <img src="https://flagcdn.com/16x12/{{ $proxy->country_code }}.png"
                                                                     alt="{{ $proxy->geolocation }}"
                                                                     class="absolute -top-1 -right-1 w-4 h-3 rounded border border-white shadow-sm"
                                                                     onerror="this.style.display='none'">
                                                            @endif
                                                        </div>

                                                        <div class="flex-1">
                                                            <!-- IP and Port -->
                                                            <div class="font-medium text-text-primary font-mono mb-1">
                                                                {{ $proxy->ip_address }}:{{ $proxy->port }}
                                                            </div>

                                                            <!-- Additional Info -->
                                                            <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-text-secondary">
                                                                @if($proxy->geolocation)
                                                                    <span class="flex items-center">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $proxy->geolocation }}
                                            </span>
                                                                @endif

                                                                @if($proxy->source === 'proxy_ipv4')
                                                                    <span class="flex items-center text-primary">
                                                <i class="fas fa-cloud mr-1"></i>
                                                ProxyIPV4
                                            </span>

                                                                    @if($proxy->expiry_date)
                                                                        @php $daysLeft = $proxy->getDaysRemaining(); @endphp
                                                                        @if($daysLeft !== null)
                                                                            <span class="flex items-center {{ $daysLeft <= 7 ? 'text-orange-600' : 'text-green-600' }}">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ $daysLeft }} days left
                                                    </span>
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    <span class="flex items-center text-purple-600">
                                                <i class="fas fa-hand-paper mr-1"></i>
                                                Manual
                                            </span>
                                                                @endif

                                                                <!-- Validation Status -->
                                                                @if($proxy->validation_status === 'valid')
                                                                    <span class="flex items-center text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ $proxy->response_time }}ms
                                            </span>
                                                                @elseif($proxy->validation_status === 'pending')
                                                                    <span class="flex items-center text-yellow-600">
                                                <i class="fas fa-clock mr-1"></i>
                                                Pending
                                            </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Selection Indicator -->
                                                    <div class="ml-auto flex items-center">
                                                        <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center transition-all duration-200"
                                                             :class="formData.proxy_id == '{{ $proxy->id }}' ? 'border-secondary bg-secondary' : 'border-gray-300'">
                                                            <div class="w-2 h-2 bg-white rounded-full transition-opacity duration-200"
                                                                 :class="formData.proxy_id == '{{ $proxy->id }}' ? 'opacity-100' : 'opacity-0'"></div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- No Available Proxies -->
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                                        <div>
                                            <div class="font-medium text-yellow-800">No available proxies</div>
                                            <div class="text-sm text-yellow-700 mt-1">
                                                All proxies are currently assigned to other email accounts.
                                                <a href="{{ route('accounts.proxy.create') }}" class="underline hover:no-underline">Add more proxies</a>
                                                or leave this field empty to proceed without a proxy.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Set proxy_id to null when no proxies available -->
                                <input type="hidden" x-model="formData.proxy_id" value="">
                            @endif

                            <div class="text-red-500 text-sm mt-2" x-show="errors.proxy_id" x-text="errors.proxy_id"></div>
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
        function emailAccountForm() {
            return {
                currentStep: 1,
                formData: {
                    provider: '',
                    email_address: '',
                    password: '',
                    proxy_id: '',
                    status: 'active',
                    first_name: '',
                    last_name: ''
                },
                errors: {},
                loading: false,
                showPassword: false,
                emailSuggestions: [],
                loadingEmailSuggestions: false,

                get progress() {
                    return ((this.currentStep - 1) / 2) * 100;
                },

                nextStep() {
                    // Clear previous errors
                    this.errors = {};

                    // Validate current step
                    if (this.currentStep === 1) {
                        if (!this.formData.provider) {
                            this.errors.provider = 'Please select an email provider.';
                            return;
                        }

                        // Generate email suggestions when moving to step 2
                        this.generateEmailSuggestions();
                    } else if (this.currentStep === 2) {
                        if (!this.formData.email_address) {
                            this.errors.email_address = 'Please enter an email address.';
                            return;
                        }
                        if (!this.validateEmail(this.formData.email_address)) {
                            this.errors.email_address = 'Please enter a valid email address.';
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

                validateEmail(email) {
                    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(String(email).toLowerCase());
                },

                togglePassword() {
                    this.showPassword = !this.showPassword;
                    const passwordInput = document.getElementById('password');
                    if (passwordInput) {
                        passwordInput.type = this.showPassword ? 'text' : 'password';
                    }
                },

                async generateEmailSuggestions() {
                    if (!this.formData.provider) {
                        return;
                    }

                    this.loadingEmailSuggestions = true;
                    this.emailSuggestions = [];

                    try {
                        // Convert provider value to lowercase for API call
                        const providerParam = this.formData.provider.toLowerCase();
                        const response = await fetch(`/api/generate-email?provider=${providerParam}`);

                        if (!response.ok) {
                            throw new Error('Failed to fetch email suggestions');
                        }

                        // Generate 5 email suggestions
                        const suggestions = [];
                        for (let i = 0; i < 5; i++) {
                            const res = await fetch(`/api/generate-email?provider=${providerParam}`);
                            if (res.ok) {
                                const data = await res.json();
                                suggestions.push(data);
                            }
                        }

                        this.emailSuggestions = suggestions;
                    } catch (error) {
                        console.error('Error generating email suggestions:', error);
                    } finally {
                        this.loadingEmailSuggestions = false;
                    }
                },

                selectEmailSuggestion(suggestion) {
                    this.formData.email_address = suggestion.email;
                    this.formData.first_name = suggestion.meta.first_name;
                    this.formData.last_name = suggestion.meta.last_name;

                    // Auto-generate password when selecting an email
                    this.generatePassword();
                },

                // Add this new method
                getEmailFeatures(email) {
                    const features = [];
                    const localPart = email.split('@')[0];

                    if (localPart.includes('_')) features.push('Underscore');
                    if (localPart.includes('.')) features.push('Dot separator');
                    if (/\d/.test(localPart)) features.push('Numbers');
                    if (/19[0-9]{2}|20[0-2][0-9]/.test(localPart)) features.push('Year');
                    if (localPart.length >= 12) features.push('Long format');
                    if (/[a-z]+\d+[a-z]+/.test(localPart)) features.push('Mixed pattern');

                    return features.slice(0, 3); // Show max 3 features
                },

                generatePassword() {
                    // Rules for password generation:
                    // 1. Use first and last name (if available)
                    // 2. Add several numbers
                    // 3. At least 1 special character
                    // 4. At least 1 capital letter
                    // 5. Password is always generated differently

                    let firstName = this.formData.first_name || '';
                    let lastName = this.formData.last_name || '';

                    // If no name is provided, use a random name
                    if (!firstName && !lastName) {
                        const randomNames = ['John', 'Jane', 'Mike', 'Anna', 'Alex', 'Emma', 'Sam', 'Lisa'];
                        firstName = randomNames[Math.floor(Math.random() * randomNames.length)];

                        const randomLastNames = ['Smith', 'Johnson', 'Brown', 'Davis', 'Wilson', 'Clark', 'Hall'];
                        lastName = randomLastNames[Math.floor(Math.random() * randomLastNames.length)];
                    }

                    // Capitalize first letter of first name
                    firstName = firstName.charAt(0).toUpperCase() + firstName.slice(1);

                    // Generate random numbers (2-3 digits)
                    const numbers = Math.floor(Math.random() * 90 + 10);

                    // Select a random special character
                    const specialChars = ['!', '@', '#', '$', '%', '&', '*'];
                    const specialChar = specialChars[Math.floor(Math.random() * specialChars.length)];

                    // Use a part of the last name (first 4 characters or the full name if shorter)
                    const lastNamePart = lastName.slice(0, Math.min(4, lastName.length));

                    // Randomly decide password format (to make it different each time)
                    const format = Math.floor(Math.random() * 4);

                    let password;
                    switch (format) {
                        case 0:
                            password = firstName + specialChar + lastNamePart + numbers;
                            break;
                        case 1:
                            password = firstName + numbers + specialChar + lastNamePart;
                            break;
                        case 2:
                            password = lastNamePart + specialChar + firstName + numbers;
                            break;
                        case 3:
                            password = lastName.charAt(0).toUpperCase() + firstName + specialChar + numbers;
                            break;
                    }

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

                    // Validate final step
                    if (!this.formData.password) {
                        this.errors.password = 'Please enter a password.';
                        return;
                    }

                    if (this.formData.password.length < 6) {
                        this.errors.password = 'Password must be at least 6 characters.';
                        return;
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