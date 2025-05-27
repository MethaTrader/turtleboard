<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TurtleBoard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/onboarding.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script defer src="{{asset('js/alpine.min.js')}}"></script>

    <!-- Stack for Page-Specific Styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-background">

    <!-- Include Sidebar Component -->
    @include('components.sidebar')

    <!-- Main Content with left margin to account for fixed sidebar -->
    <div class="flex-1 flex flex-col overflow-hidden md:ml-64">
        <!-- Header -->
        <header class="bg-background py-4 px-6">
            <div class="flex justify-between items-center">
                <!-- Toggle Button & Welcome -->
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="mr-4 text-text-secondary focus:outline-none lg:hidden">
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
                        <input type="text" placeholder="Search" class="w-64 bg-card rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 border border-gray-200">
                        <i class="fas fa-search absolute left-4 top-3 text-text-secondary"></i>
                    </div>

                    <!-- Tour Help Button -->
                    <button id="start-tour-button" class="tour-help-button" title="Start Tour">
                        <i class="fas fa-question"></i>
                    </button>

                    <div class="relative">
                        <button class="relative">
                            <i class="fas fa-bell text-text-secondary text-xl hover:text-primary transition-colors duration-200"></i>
                            <span class="absolute -top-1 -right-1 bg-danger text-white text-xs w-4 h-4 flex items-center justify-center rounded-full">3</span>
                        </button>
                    </div>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 hover:opacity-80 transition-opacity duration-200">
                            <span class="text-sm font-medium text-text-primary">{{ Auth::user()->name }}</span>
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=5A55D2&color=fff" alt="Profile" class="h-8 w-8 rounded-full">
                            <i class="fas fa-chevron-down text-xs text-text-secondary transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-48 bg-card rounded-card shadow-dropdown py-2 z-50"
                             style="display: none;">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-text-primary hover:bg-background transition-colors duration-150">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-text-primary hover:bg-background transition-colors duration-150">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <button id="start-tour-menu" class="block w-full text-left px-4 py-2 text-sm text-text-primary hover:bg-background transition-colors duration-150">
                                <i class="fas fa-question-circle mr-2"></i> Take Tour
                            </button>
                            <div class="border-t border-gray-200 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-danger hover:bg-background transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
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

<script>
    // Mobile sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                // Simple toggle for mobile sidebar
                const sidebar = document.querySelector('[x-data*="sidebarOpen"]');
                if (sidebar && window.Alpine) {
                    window.Alpine.evaluate(sidebar, 'sidebarOpen = !sidebarOpen');
                }
            });
        }

        // Add event listener for the tour button in menu
        const tourMenuButton = document.getElementById('start-tour-menu');
        if (tourMenuButton) {
            tourMenuButton.addEventListener('click', function() {
                // This will trigger the tour through the onboarding.js functionality
                if (typeof window.initTour === 'function') {
                    window.initTour();
                } else {
                    // Fallback if the function isn't directly accessible
                    document.dispatchEvent(new CustomEvent('start-tour'));
                }
            });
        }
    });
</script>
</body>
</html>