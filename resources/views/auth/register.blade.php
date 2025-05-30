<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TurtleBoard') }} - Register</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="font-sans antialiased bg-background min-h-screen">
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4 p-3">
                <img src="{{ asset('images/turtle.png') }}" alt="TurtleBoard Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl font-bold text-text-primary">Join TurtleBoard</h1>
            <p class="text-text-secondary mt-2">Create your account to get started</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-card rounded-2xl shadow-card p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- SSO Code -->
                <div>
                    <label for="sso_code" class="block text-sm font-medium text-text-primary mb-2">
                        Access Code
                    </label>
                    <div class="relative">
                        <input id="sso_code" type="text" name="sso_code" value="{{ old('sso_code') }}"
                               required autofocus maxlength="5" placeholder="Enter 5-character code"
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 @error('sso_code') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                    </div>
                    @error('sso_code')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-text-primary mb-2">
                        Full Name
                    </label>
                    <div class="relative">
                        <input id="name" type="text" name="name" value="{{ old('name') }}"
                               required autocomplete="name" placeholder="Enter your full name"
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 @error('name') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                    </div>
                    @error('name')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-text-primary mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               required autocomplete="username" placeholder="Enter your email"
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 @error('email') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                    @error('email')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-text-primary mb-2">
                        Account Type
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="administrator" {{ old('role') == 'administrator' ? 'checked' : '' }}
                            class="sr-only peer" required>
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center transition-all peer-checked:border-primary peer-checked:bg-primary/5">
                                <i class="fas fa-crown text-2xl text-gray-400 peer-checked:text-primary mb-2"></i>
                                <div class="font-medium text-sm">Administrator</div>
                                <div class="text-xs text-gray-500">Full access</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="account_manager" {{ old('role') == 'account_manager' ? 'checked' : '' }}
                            class="sr-only peer" required>
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center transition-all peer-checked:border-primary peer-checked:bg-primary/5">
                                <i class="fas fa-user-tie text-2xl text-gray-400 peer-checked:text-primary mb-2"></i>
                                <div class="font-medium text-sm">Manager</div>
                                <div class="text-xs text-gray-500">Account access</div>
                            </div>
                        </label>
                    </div>
                    @error('role')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-text-primary mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               placeholder="Create a secure password"
                               class="w-full px-4 py-3 pl-12 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 @error('password') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i id="password-toggle-icon" class="fas fa-eye text-gray-400 hover:text-primary transition-colors"></i>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-text-primary mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               autocomplete="new-password" placeholder="Confirm your password"
                               class="w-full px-4 py-3 pl-12 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 @error('password_confirmation') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-check-double text-gray-400"></i>
                        </div>
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i id="password_confirmation-toggle-icon" class="fas fa-eye text-gray-400 hover:text-primary transition-colors"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Register Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-4 rounded-lg font-medium hover:from-primary/90 hover:to-secondary/90 focus:ring-2 focus:ring-primary/20 transition-all duration-200 transform hover:scale-[1.02] shadow-lg mt-6">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>

                <!-- Login Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-text-secondary text-sm">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-primary hover:text-primary/80 font-medium transition-colors">
                            Sign In
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Simple Info Footer -->
        <div class="text-center mt-8">
            <p class="text-text-secondary text-sm">
                By creating an account, you can manage MEXC accounts, proxies, and Web3 wallets in one place.
            </p>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ssoCodeInput = document.getElementById('sso_code');

        if (ssoCodeInput) {
            // Auto-uppercase and limit to 5 characters
            ssoCodeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase().slice(0, 5);
            });

            // Only allow alphanumeric characters
            ssoCodeInput.addEventListener('keypress', function(e) {
                const char = String.fromCharCode(e.which);
                if (!/[A-Za-z0-9]/.test(char)) {
                    e.preventDefault();
                }
            });
        }

        // Update icons for role selection
        const roleInputs = document.querySelectorAll('input[name="role"]');
        roleInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Update visual feedback when role is selected
                roleInputs.forEach(r => {
                    const icon = r.parentElement.querySelector('i');
                    if (r.checked) {
                        icon.classList.remove('text-gray-400');
                        icon.classList.add('text-primary');
                    } else {
                        icon.classList.remove('text-primary');
                        icon.classList.add('text-gray-400');
                    }
                });
            });
        });
    });

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '-toggle-icon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>