<div x-data="{ sidebarOpen: true, activeMenu: '{{ request()->route()->getName() }}' }"
     class="bg-sidebar w-64 shadow-sidebar fixed inset-y-0 left-0 z-30 transform md:translate-x-0 transition duration-200 ease-in-out"
     :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">

    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b border-gray-100">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <img src="{{ asset('images/turtle.png') }}" alt="TurtleBoard Logo" class="w-10 h-10 mr-3">
                <span class="text-xl font-bold text-secondary">TurtleBoard</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'dashboard'}">
                        <i class="fas fa-home text-lg w-6"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 text-xs font-semibold text-text-muted uppercase tracking-wider">
                        Account Management
                    </div>
                </li>

                <li>
                    <a href="{{ route('accounts.mexc') }}"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'accounts.mexc'}">
                        <i class="fas fa-wallet text-lg w-6"></i>
                        <span class="ml-3">MEXC Accounts</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accounts.email') }}"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'accounts.email'}">
                        <i class="fas fa-envelope text-lg w-6"></i>
                        <span class="ml-3">Email Accounts</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accounts.proxy') }}"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'accounts.proxy'}">
                        <i class="fas fa-server text-lg w-6"></i>
                        <span class="ml-3">Proxies</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accounts.web3') }}"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'accounts.web3'}">
                        <i class="fas fa-link text-lg w-6"></i>
                        <span class="ml-3">Web3 Wallets</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 text-xs font-semibold text-text-muted uppercase tracking-wider">
                        Analytics
                    </div>
                </li>

                <li>
                    <a href="{{ route('referrals.index') }}"
                       class="sidebar-item {{ request()->routeIs('referrals.*') ? 'active' : '' }}">
                        <i class="fas fa-users mr-3 w-5 text-center"></i>
                        <span>Referrals</span>
                    </a>
                </li>

                <li class="opacity-40">
                    <a href="{{ route('relationships') }}"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'relationships'}">
                        <i class="fas fa-project-diagram text-lg w-6"></i>
                        <span class="ml-3">Relationships</span>
                    </a>
                </li>

                <li class="opacity-40">
                    <a href="#"
                       class="sidebar-item"
                       :class="{'active': activeMenu === 'validation'}">
                        <i class="fas fa-check-circle text-lg w-6"></i>
                        <span class="ml-3">KPI</span>
                    </a>
                </li>

                @if(Auth::user()->isAdmin())
                    <li class="pt-4">
                        <div class="px-4 text-xs font-semibold text-text-muted uppercase tracking-wider">
                            Administration
                        </div>
                    </li>

                    <li>
                        <a href="{{ route('admin.users.index') }}"
                           class="sidebar-item"
                           :class="{'active': activeMenu === 'admin.users.index'}">
                            <i class="fas fa-users text-lg w-6"></i>
                            <span class="ml-3">User Management</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.settings') }}"
                           class="sidebar-item"
                           :class="{'active': activeMenu === 'admin.settings'}">
                            <i class="fas fa-cog text-lg w-6"></i>
                            <span class="ml-3">Settings</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <!-- User Information -->
        <div class="border-t border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=5A55D2&color=fff" alt="Profile" class="h-8 w-8 rounded-full">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-text-primary">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-text-secondary">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="text-text-secondary hover:text-danger transition-colors duration-200 p-1 rounded-md hover:bg-background" title="Logout">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>