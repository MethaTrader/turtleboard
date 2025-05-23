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

                <form action="{{ route('accounts.email.store') }}" method="POST" @submit.prevent="submitForm">
                    @csrf
                    <input type="hidden" name="step" :value="stepNames[currentStep-1]">

                    <!-- Step 1: Select Provider -->
                    <div x-show="currentStep === 1">
                        <h3 class="text-lg font-semibold mb-4">Select Email Provider</h3>
                        <p class="text-text-secondary mb-6">Choose the email service provider for your account.</p>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            @foreach($providers as $provider)
                                <div class="relative">
                                    <input type="radio" id="provider_{{ $provider }}" name="provider" value="{{ $provider }}"
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
                                                @else fa-envelope
                                                @endif"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-text-primary font-medium">{{ $provider }}</div>
                                                <div class="text-text-secondary text-xs">
                                                    @if($provider == 'Gmail') Google Mail Service
                                                    @elseif($provider == 'Outlook') Microsoft Email
                                                    @elseif($provider == 'Yahoo') Yahoo Mail
                                                    @else Rambler Mail
                                                    @endif
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

                        <div class="text-red-500 text-sm mt-2" x-show="errors.provider" x-text="errors.provider"></div>
                    </div>

                    <!-- Step 2: Enter Email Address -->
                    <div x-show="currentStep === 2">
                        <h3 class="text-lg font-semibold mb-4">Enter Email Address</h3>
                        <p class="text-text-secondary mb-6">Please provide the email address for your <span x-text="formData.provider"></span> account.</p>

                        <div class="mb-6">
                            <label for="email_address" class="block text-sm font-medium text-text-secondary mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="email_address" name="email_address" x-model="formData.email_address"
                                       class="block w-full pl-10 rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                       placeholder="example@{{ formData.provider.toLowerCase() }}.com">
                            </div>
                            <div class="text-red-500 text-sm mt-2" x-show="errors.email_address" x-text="errors.email_address"></div>
                        </div>
                    </div>

                    <!-- Step 3: Enter Password -->
                    <div x-show="currentStep === 3">
                        <h3 class="text-lg font-semibold mb-4">Enter Password</h3>
                        <p class="text-text-secondary mb-6">Please provide the password for <span x-text="formData.email_address"></span>.</p>

                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-text-secondary mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="password" name="password" x-model="formData.password"
                                       class="block w-full pl-10 rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                       placeholder="Enter your password">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="togglePassword">
                                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" title="Toggle password visibility"></i>
                                </div>
                            </div>
                            <div class="text-red-500 text-sm mt-2" x-show="errors.password" x-text="errors.password"></div>
                        </div>

                        <div class="mb-6">
                            <label for="proxy_id" class="block text-sm font-medium text-text-secondary mb-1">Proxy (Optional)</label>
                            <select id="proxy_id" name="proxy_id" x-model="formData.proxy_id"
                                    class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">No Proxy</option>
                                @foreach($availableProxies as $proxy)
                                    <option value="{{ $proxy->id }}">{{ $proxy->ip_address }}:{{ $proxy->port }}</option>
                                @endforeach
                            </select>
                            <div class="text-text-secondary text-xs mt-1">
                                Associate a proxy with this email account (optional).
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
                                    :disabled="loading"
                                    x-html="loading ? '<i class=\'fas fa-spinner fa-spin mr-2\'></i> Saving...' : '<i class=\'fas fa-save mr-2\'></i> Save Account'">
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
                stepNames: ['provider', 'email', 'password'],
                formData: {
                    provider: '',
                    email_address: '',
                    password: '',
                    proxy_id: '',
                    status: 'active'
                },
                errors: {},
                loading: false,
                showPassword: false,

                get progress() {
                    return ((this.currentStep - 1) / 2) * 100;
                },

                nextStep() {
                    if (this.currentStep === 1) {
                        if (!this.formData.provider) {
                            this.errors.provider = 'Please select an email provider.';
                            return;
                        }
                        this.errors.provider = '';
                    } else if (this.currentStep === 2) {
                        if (!this.formData.email_address) {
                            this.errors.email_address = 'Please enter an email address.';
                            return;
                        }
                        if (!this.validateEmail(this.formData.email_address)) {
                            this.errors.email_address = 'Please enter a valid email address.';
                            return;
                        }
                        this.errors.email_address = '';
                    }

                    this.currentStep++;
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },

                validateEmail(email) {
                    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(String(email).toLowerCase());
                },

                togglePassword() {
                    this.showPassword = !this.showPassword;
                    const passwordInput = document.getElementById('password');
                    passwordInput.type = this.showPassword ? 'text' : 'password';
                },

                async validateStep(step) {
                    this.errors = {};

                    // Create form data
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    formData.append('step', this.stepNames[step-1]);

                    if (step >= 1) formData.append('provider', this.formData.provider);
                    if (step >= 2) formData.append('email_address', this.formData.email_address);
                    if (step >= 3) {
                        formData.append('password', this.formData.password);
                        if (this.formData.proxy_id) formData.append('proxy_id', this.formData.proxy_id);
                        formData.append('status', this.formData.status);
                    }

                    try {
                        const response = await fetch("{{ route('accounts.email.store') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (data.errors) {
                                this.errors = data.errors;
                                return false;
                            }
                        }

                        return true;
                    } catch (error) {
                        console.error('Validation error:', error);
                        return false;
                    }
                },

                async finalSubmit() {
                    if (!this.formData.password) {
                        this.errors.password = 'Please enter a password.';
                        return;
                    }

                    this.loading = true;

                    // Validate before final submission
                    const isValid = await this.validateStep(3);

                    if (isValid) {
                        // Submit the form
                        document.querySelector('form').submit();
                    } else {
                        this.loading = false;
                    }
                },

                submitForm(event) {
                    // This is handled by finalSubmit
                }
            }
        }
    </script>
@endpush