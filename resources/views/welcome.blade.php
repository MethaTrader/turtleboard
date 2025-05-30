<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TurtleBoard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="font-sans antialiased bg-background">
<!-- Header -->
<header class="bg-card shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mr-3 p-2">
                    <img src="{{ asset('images/turtle.png') }}" alt="TurtleBoard Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-bold text-text-primary">TurtleBoard</h1>
                    <p class="text-xs text-text-secondary">MEXC Account Management</p>
                </div>
            </div>

            <!-- Auth Links -->
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-text-primary hover:text-secondary font-medium transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="bg-secondary hover:bg-secondary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="min-h-screen">
    <!-- Hero Section -->
    <section class="py-20">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <!-- Logo -->
            <div class="w-32 h-32  rounded-2xl flex items-center justify-center mx-auto mb-8 p-4">
                <img src="{{ asset('images/turtle.png') }}" alt="TurtleBoard Logo" class="w-full h-full object-contain">
            </div>

            <!-- Main Heading -->
            <h1 class="text-4xl md:text-5xl font-bold text-text-primary mb-6">
                Manage Your MEXC Accounts
                <span class="text-primary">Efficiently</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-xl text-text-secondary mb-10 max-w-2xl mx-auto">
                Centralized dashboard for managing cryptocurrency exchange accounts, proxies, and Web3 wallets with enterprise-grade security.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-gradient-to-r from-primary to-secondary text-white px-8 py-3 rounded-lg font-medium hover:from-primary/90 hover:to-secondary/90 transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-gradient-to-r from-primary to-secondary text-white px-8 py-3 rounded-lg font-medium hover:from-primary/90 hover:to-secondary/90 transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>Get Started
                    </a>
                    <a href="{{ route('login') }}" class="border-2 border-secondary text-secondary px-8 py-3 rounded-lg font-medium hover:bg-secondary hover:text-white transition-all">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-card">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-text-primary mb-4">Everything You Need</h2>
                <p class="text-text-secondary">Powerful tools for managing your cryptocurrency accounts</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- MEXC Management -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-wallet text-secondary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">MEXC Accounts</h3>
                    <p class="text-text-secondary text-sm">Centralized management of your exchange accounts with secure credential storage.</p>
                </div>

                <!-- Email Management -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-purple-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Email Accounts</h3>
                    <p class="text-text-secondary text-sm">Link and manage email accounts across multiple providers with encryption.</p>
                </div>

                <!-- Proxy Management -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-server text-orange-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Proxy Servers</h3>
                    <p class="text-text-secondary text-sm">Configure and validate proxy connections with real-time status monitoring.</p>
                </div>

                <!-- Web3 Wallets -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-link text-amber-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Web3 Wallets</h3>
                    <p class="text-text-secondary text-sm">Secure blockchain wallet management with multi-network support.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <div class="w-16 h-16 bg-success/10 rounded-xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shield-alt text-success text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-text-primary mb-4">Enterprise Security</h2>
            <p class="text-xl text-text-secondary mb-8">
                Your sensitive data is protected with industry-standard encryption, role-based access control, and secure authentication.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div class="flex items-center justify-center">
                    <i class="fas fa-lock text-success mr-2"></i>
                    <span class="text-text-secondary">End-to-End Encryption</span>
                </div>
                <div class="flex items-center justify-center">
                    <i class="fas fa-users-cog text-success mr-2"></i>
                    <span class="text-text-secondary">Role-Based Access</span>
                </div>
                <div class="flex items-center justify-center">
                    <i class="fas fa-key text-success mr-2"></i>
                    <span class="text-text-secondary">Secure Authentication</span>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Footer -->
<footer class="bg-card border-t border-gray-200 py-8">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center mb-4">
            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-2 p-1">
                <img src="{{ asset('images/turtle.png') }}" alt="TurtleBoard Logo" class="w-full h-full object-contain">
            </div>
            <span class="font-semibold text-text-primary">TurtleBoard</span>
        </div>
        <p class="text-text-secondary text-sm">
            Professional MEXC account management dashboard built with Laravel.
        </p>
        <p class="text-text-secondary text-xs mt-2">
            &copy; {{ date('Y') }} TurtleBoard. Slow and steady wins the race.
        </p>
    </div>
</footer>

<!-- Simple fade-in animation -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add fade-in animation to main sections
        const sections = document.querySelectorAll('section');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        });
    });
</script>
</body>
</html>