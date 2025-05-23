<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Add CSP meta tag to allow needed connections -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' http: https: data: blob: ws: wss: 'unsafe-inline';">

    <title>{{ config('app.name', 'TurtleBoard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Stack for Page-Specific Styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-background flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-sidebar shadow-sidebar fixed inset-y-0 left-0 z-10 transform md:relative md:translate-x-0 transition duration-200 ease-in-out" id="sidebar">
        <div class="h-screen flex flex-col sticky top-0">
            <!-- Logo -->
            <div class="flex items-center h-16 px-6 border-b">
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <div class="turtle-icon mr-3 flex items-center justify-center">
                        <span class="relative z-10 text-white font-bold text-xs">🐢</span>
                    </div>
                    <span class="text-xl font-bold">TurtleBoard</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('dashboard') }}"
                           class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie text-lg w-6"></i>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('accounts.mexc') }}"
                           class="sidebar-item {{ request()->routeIs('accounts.mexc*') ? 'active' : '' }}">
                            <i class="fas fa-wallet text-lg w-6"></i>
                            <span class="ml-3">MEXC Accounts</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('accounts.email') }}"
                           class="sidebar-item {{ request()->routeIs('accounts.email*') ? 'active' : '' }}">
                            <i class="fas fa-envelope text-lg w-6"></i>
                            <span class="ml-3">Email Accounts</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('accounts.proxy') }}"
                           class="sidebar-item {{ request()->routeIs('accounts.proxy*') ? 'active' : '' }}">
                            <i class="fas fa-server text-lg w-6"></i>
                            <span class="ml-3">Proxies</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('accounts.web3') }}"
                           class="sidebar-item {{ request()->routeIs('accounts.web3*') ? 'active' : '' }}">
                            <i class="fas fa-link text-lg w-6"></i>
                            <span class="ml-3">Web3 Wallets</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.settings') }}"
                           class="sidebar-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                            <i class="fas fa-cog text-lg w-6"></i>
                            <span class="ml-3">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Security Card -->
            <div class="p-4 mb-6">
                <div class="bg-primary p-4 rounded-card text-white relative overflow-hidden">
                    <div class="absolute top-2 right-2 w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="font-semibold mb-2">Keep you safe!</h4>
                    <p class="text-xs mb-3">Update your security password, keep your account safe!</p>
                    <button class="bg-white text-primary py-2 px-4 rounded-lg text-sm font-medium">Update Privacy</button>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-background py-4 px-6">
            <div class="flex justify-between items-center">
                <!-- Toggle Button & Welcome -->
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="mr-4 text-gray-600 focus:outline-none lg:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl font-semibold text-text-primary">Hello {{ Auth::user()->name }}</h1>
                        <p class="text-sm text-text-secondary">{{ now()->format('g:i a d M Y') }}</p>
                    </div>
                </div>

                <!-- Search & User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search" class="w-64 bg-white rounded-full py-2 pl-10 pr-4 focus:outline-none">
                        <i class="fas fa-search absolute left-4 top-3 text-text-secondary"></i>
                    </div>

                    <div class="relative">
                        <button class="relative">
                            <i class="fas fa-bell text-text-secondary text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-danger text-white text-xs w-4 h-4 flex items-center justify-center rounded-full">3</span>
                        </button>
                    </div>

                    <div class="relative" id="user-profile-dropdown">
                        <button id="profile-dropdown-btn" class="flex items-center space-x-2">
                            <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=5A55D2&color=fff" alt="Profile" class="h-8 w-8 rounded-full">
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="dropdown-menu" class="user-dropdown">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 bg-background p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>

<!-- Stack for Page-Specific Scripts -->
@stack('scripts')
</body>
</html>