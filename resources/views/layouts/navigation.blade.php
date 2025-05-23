<!-- Add this after the Dashboard link -->
@if (Auth::user()->isAdmin())
    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
        {{ __('User Management') }}
    </x-nav-link>
@endif

<!-- Add your role-specific menu items here -->
@if (Auth::user()->isAdmin() || Auth::user()->isAccountManager())
    <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
        {{ __('Accounts') }}
    </x-nav-link>
@endif