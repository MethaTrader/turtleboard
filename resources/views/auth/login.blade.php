<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TurtleBoard') }} - Login</title>

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
<div class="min-h-screen flex">
    <!-- Left Side - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-secondary via-secondary to-primary relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)" />
            </svg>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-center items-center text-white p-12">
            <!-- Logo -->
            <div class="mb-8">
                <div class="w-32 h-32 bg-secondary p-2 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                    <img src="{{ asset('images/turtle.png') }}" alt="TurtleBoard Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-4xl font-bold">TurtleBoard</h1>
                <p class="text-white/80 text-lg">MEXC Account Management Dashboard</p>
            </div>

            <!-- Features -->
            <div class="space-y-6 max-w-md">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Manage MEXC Accounts</h3>
                        <p class="text-white/70 text-sm">Centralized cryptocurrency exchange account management</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Secure & Encrypted</h3>
                        <p class="text-white/70 text-sm">Your sensitive data is protected with enterprise-grade security</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Team Collaboration</h3>
                        <p class="text-white/70 text-sm">Work together with role-based access control</p>
                    </div>
                </div>
            </div>

            <!-- Footer Quote -->
            <div class="mt-12 text-center">
                <p class="text-white/60 italic">"Slow and steady wins the race"</p>
                <p class="text-white/40 text-sm mt-2">- The Philosophy of TurtleBoard</p>
            </div>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <!-- Mobile Logo -->
            <div class="lg:hidden text-center mb-8">
                <div class="w-16 h-16 bg-secondary rounded-xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-turtle text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-text-primary">TurtleBoard</h1>
                <p class="text-text-secondary">MEXC Account Management</p>
            </div>

            <!-- Login Header -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-text-primary">Welcome Back</h2>
                <p class="text-text-secondary mt-2">Sign in to your TurtleBoard account</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-success/10 border border-success/20 rounded-lg text-success text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- SSO Code -->
                <div>
                    <label for="sso_code" class="block text-sm font-medium text-text-primary mb-2">
                        <i class="fas fa-key mr-2 text-secondary"></i>SSO Code
                    </label>
                    <div class="relative">
                        <input id="sso_code" type="text" name="sso_code" value="{{ old('sso_code') }}" required autofocus maxlength="5" placeholder="Enter 5-character code"
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all duration-200 @error('sso_code') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-shield-alt text-gray-400"></i>
                        </div>
                    </div>
                    @error('sso_code')
                    <p class="mt-2 text-sm text-danger flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                    @enderror
                    <p class="mt-1 text-xs text-text-secondary">Enter the 5-character SSO code to proceed with login.</p>
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-text-primary mb-2">
                        <i class="fas fa-envelope mr-2 text-secondary"></i>Email Address
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your email"
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all duration-200 @error('email') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-at text-gray-400"></i>
                        </div>
                    </div>
                    @error('email')
                    <p class="mt-2 text-sm text-danger flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-text-primary mb-2">
                        <i class="fas fa-lock mr-2 text-secondary"></i>Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"
                               class="w-full px-4 py-3 pl-12 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all duration-200 @error('password') border-danger @enderror">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                        <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i id="password-toggle-icon" class="fas fa-eye text-gray-400 hover:text-secondary transition-colors"></i>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-2 text-sm text-danger flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-secondary border-gray-300 rounded focus:ring-secondary/20">
                        <span class="ml-2 text-sm text-text-secondary">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-secondary hover:text-secondary/80 transition-colors" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-secondary to-primary text-white py-3 px-4 rounded-lg font-medium hover:from-secondary/90 hover:to-primary/90 focus:ring-2 focus:ring-secondary/20 transition-all duration-200 transform hover:scale-[1.02] shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>

                <!-- Register Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-text-secondary text-sm">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-secondary hover:text-secondary/80 font-medium transition-colors">
                            Create Account
                        </a>
                    </p>
                </div>
            </form>
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