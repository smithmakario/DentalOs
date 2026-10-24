@php
    $navLinkClasses = fn (bool $active): string => $active
        ? 'flex items-center gap-3 px-4 py-3 text-white bg-blue-600 rounded-lg mx-2 border-l-4 border-white translate-x-1 transition-all duration-200'
        : 'flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white mx-2 hover:bg-slate-800 transition-all duration-200';
@endphp

<aside class="fixed left-0 top-0 h-full flex flex-col py-6 bg-slate-900 w-64 border-r border-slate-800 shadow-xl z-50">
    <div class="px-6 mb-8">
        <a href="{{ route('clinic.dashboard') }}">
            <h1 class="text-lg font-black text-white">{{ config('app.name', 'DentalOs') }}</h1>
            <p class="text-xs text-slate-400 truncate">{{ $clinicOrganization->name ?? __('Clinic') }}</p>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto">
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.dashboard')) }}" href="{{ route('clinic.dashboard') }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span>{{ __('Dashboard') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.patients.*')) }}" href="{{ route('clinic.patients.index') }}">
            <span class="material-symbols-outlined">groups</span>
            <span>{{ __('Patients') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.appointments.*')) }}" href="{{ route('clinic.appointments.index') }}">
            <span class="material-symbols-outlined">calendar_month</span>
            <span>{{ __('Appointments') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.treatments.*')) }}" href="{{ route('clinic.treatments.index') }}">
            <span class="material-symbols-outlined">medical_services</span>
            <span>{{ __('Treatments') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.services.*')) }}" href="{{ route('clinic.services.index') }}">
            <span class="material-symbols-outlined">list_alt</span>
            <span>{{ __('Services') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.staff.*')) }}" href="{{ route('clinic.staff.index') }}">
            <span class="material-symbols-outlined">badge</span>
            <span>{{ __('Staff') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.branches.*')) }}" href="{{ route('clinic.branches.index') }}">
            <span class="material-symbols-outlined">account_tree</span>
            <span>{{ __('Clinic Branches') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs(['clinic.billing.*', 'clinic.transactions.*'])) }}" href="{{ route('clinic.transactions.index') }}">
            <span class="material-symbols-outlined">payments</span>
            <span>{{ __('Transactions') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.reconciliation')) }}" href="{{ route('clinic.reconciliation') }}">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span>{{ __('Reconciliation') }}</span>
        </a>
        <a class="{{ $navLinkClasses(request()->routeIs('clinic.support')) }}" href="{{ route('clinic.support') }}">
            <span class="material-symbols-outlined">support_agent</span>
            <span>{{ __('Contact Support') }}</span>
        </a>
    </nav>

    <div class="mt-auto px-4 space-y-3">
        @auth('staff')
            <div class="border-t border-slate-800 pt-3 space-y-1">
                <p class="px-2 text-xs text-slate-500 truncate">{{ auth('staff')->user()->full_name }}</p>
                <form method="POST" action="{{ route('clinic.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white mx-2 hover:bg-slate-800 transition-all duration-200 rounded-lg">
                        <span class="material-symbols-outlined">logout</span>
                        <span>{{ __('Log Out') }}</span>
                    </button>
                </form>
            </div>
        @endauth
    </div>
</aside>
