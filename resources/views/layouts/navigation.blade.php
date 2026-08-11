@php
    $navLinkClasses = fn (bool $active): string => $active
        ? 'flex items-center gap-3 px-4 py-3 text-white bg-blue-600 rounded-lg mx-2 border-l-4 border-white translate-x-1 transition-all duration-200'
        : 'flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white mx-2 hover:bg-slate-800 transition-all duration-200';
@endphp

<aside class="fixed left-0 top-0 h-full flex flex-col py-6 bg-slate-900 w-64 border-r border-slate-800 shadow-xl z-50">
    <div class="px-6 mb-8">
        <a href="{{ route('dashboard') }}">
            <h1 class="text-lg font-black text-white">DentaFlow</h1>
            <p class="text-xs text-slate-400">Enterprise Admin</p>
        </a>
    </div>

    <nav class="flex-1 space-y-1">
        <a class="{{ $navLinkClasses(request()->routeIs('dashboard')) }}" href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
            <span>{{ __('Dashboard') }}</span>
        </a>

        <a class="{{ $navLinkClasses(request()->routeIs('clinics.*')) }}" href="{{ route('clinics.index') }}">
            <span class="material-symbols-outlined" data-icon="medical_services">medical_services</span>
            <span>{{ __('Clinics') }}</span>
        </a>

        <a class="{{ $navLinkClasses(request()->routeIs('subscriptions.*')) }}" href="{{ route('subscriptions.index') }}">
            <span class="material-symbols-outlined" data-icon="payments">payments</span>
            <span>{{ __('Subscription') }}</span>
        </a>

        <a class="{{ $navLinkClasses(request()->routeIs('analytics.*')) }}" href="{{ route('analytics.index') }}">
            <span class="material-symbols-outlined" data-icon="monitoring">monitoring</span>
            <span>{{ __('Analytics') }}</span>
        </a>

        @can('viewAny', App\Models\StaffMember::class)
            <a class="{{ $navLinkClasses(request()->routeIs('staff.*') || request()->routeIs('clinics.staff.*')) }}" href="{{ route('staff.index') }}">
                <span class="material-symbols-outlined" data-icon="group">group</span>
                <span>{{ __('Staff') }}</span>
            </a>
        @endcan

        <a class="{{ $navLinkClasses(request()->routeIs('audit-logs.*')) }}" href="{{ route('audit-logs.index') }}">
            <span class="material-symbols-outlined" data-icon="history">history</span>
            <span>{{ __('Audit Log') }}</span>
        </a>
    </nav>

    <div class="mt-auto px-4 space-y-3">
        <button type="button" class="w-full py-3 bg-slate-800 text-white rounded-lg font-label-md flex items-center justify-center gap-2 hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined text-sm" data-icon="support_agent">support_agent</span>
            {{ __('Support Portal') }}
        </button>

        @auth
            <div class="border-t border-slate-800 pt-3 space-y-1">
                <p class="px-2 text-xs text-slate-500 truncate">{{ Auth::user()->name }}</p>

                <a class="{{ $navLinkClasses(request()->routeIs('profile.*')) }}" href="{{ route('profile.edit') }}">
                    <span class="material-symbols-outlined" data-icon="person">person</span>
                    <span>{{ __('Profile') }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white mx-2 hover:bg-slate-800 transition-all duration-200 rounded-lg">
                        <span class="material-symbols-outlined" data-icon="logout">logout</span>
                        <span>{{ __('Log Out') }}</span>
                    </button>
                </form>
            </div>
        @endauth
    </div>
</aside>
