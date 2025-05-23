<div x-data="{ sidebarOpen: true, activeMenu: '{{ request()->route()->getName() }}' }"
     class="bg-white w-64 shadow-sm fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out"
     :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">

    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b border-gray-100">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <span class="text-2xl font-bold text-[#5A55D2]">MEXC</span>
                <span class="ml-1 text-[#00DEA3] font-bold">Manager</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'dashboard'}">
                        <i class="fas fa-home text-lg w-6"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Account Management
                    </div>
                </li>

                <li>
                    <a href="{{ route('accounts.mexc') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'accounts.mexc'}">
                        <i class="fas fa-wallet text-lg w-6"></i>
                        <span class="ml-3">MEXC Accounts</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accounts.email') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'accounts.email'}">
                        <i class="fas fa-envelope text-lg w-6"></i>
                        <span class="ml-3">Email Accounts</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accounts.proxy') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'accounts.proxy'}">
                        <i class="fas fa-server text-lg w-6"></i>
                        <span class="ml-3">Proxies</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accounts.web3') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'accounts.web3'}">
                        <i class="fas fa-link text-lg w-6"></i>
                        <span class="ml-3">Web3 Wallets</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Analytics
                    </div>
                </li>

                <li>
                    <a href="{{ route('relationships') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'relationships'}">
                        <i class="fas fa-project-diagram text-lg w-6"></i>
                        <span class="ml-3">Relationships</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('validation') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                       :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'validation'}">
                        <i class="fas fa-check-circle text-lg w-6"></i>
                        <span class="ml-3">Validation</span>
                    </a>
                </li>

                @if(Auth::user()->isAdmin())
                    <li class="pt-4">
                        <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Administration
                        </div>
                    </li>

                    <li>
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                           :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'admin.users.index'}">
                            <i class="fas fa-users text-lg w-6"></i>
                            <span class="ml-3">User Management</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.settings') }}"
                           class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-[#EFF3FD] transition-colors"
                           :class="{'bg-[#EFF3FD] text-[#5A55D2] font-medium': activeMenu === 'admin.settings'}">
                            <i class="fas fa-cog text-lg w-6"></i>
                            <span class="ml-3">Settings</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <!-- User Information -->
        <div class="border-t border-gray-100 p-4">
            <div class="flex items-center">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=5A55D2&color=fff" alt="Profile" class="h-8 w-8 rounded-full">
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>