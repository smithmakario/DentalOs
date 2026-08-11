@php
    $navLinkClasses = fn (bool $active): string => $active
        ? 'flex items-center gap-3 px-4 py-3 text-white bg-blue-600 rounded-lg mx-2 border-l-4 border-white translate-x-1 transition-all duration-200'
        : 'flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white mx-2 hover:bg-slate-800 transition-all duration-200';
@endphp

<aside class="fixed left-0 top-0 h-full flex flex-col py-6 bg-slate-900 w-64 border-r border-slate-800 shadow-xl z-50">
    <div class="px-6 mb-8">
        <a href="{{ route('tenant.dashboard') }}">
            <h1 class="text-lg font-black text-white">DentaFlow</h1>
            <p class="text-xs text-slate-400 truncate">{{ tenant('name') }}</p>
        </a>
    </div>

    <nav class="flex-1 space-y-1">
        <a class="{{ $navLinkClasses(request()->routeIs('tenant.dashboard')) }}" href="{{ route('tenant.dashboard') }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span>{{ __('Dashboard') }}</span>
        </a>

        @if ($staffCanViewPatients ?? false)
            <a class="{{ $navLinkClasses(request()->routeIs('tenant.patients.*')) }}" href="{{ route('tenant.patients.index') }}">
                <span class="material-symbols-outlined">groups</span>
                <span>{{ __('Patients') }}</span>
            </a>
        @endif

        @if ($staffCanManageAppointments ?? false)
            <a class="{{ $navLinkClasses(request()->routeIs('tenant.appointments.*')) }}" href="{{ route('tenant.appointments.index') }}">
                <span class="material-symbols-outlined">calendar_month</span>
                <span>{{ __('Appointments') }}</span>
            </a>
        @endif

        @if ($staffCanViewTreatments ?? false)
            <a class="{{ $navLinkClasses(request()->routeIs('tenant.treatment-plans.*')) }}" href="{{ route('tenant.treatment-plans.index') }}">
                <span class="material-symbols-outlined">medical_services</span>
                <span>{{ __('Treatments') }}</span>
            </a>
        @endif

        @if ($staffCanManageServices ?? false)
            <a class="{{ $navLinkClasses(request()->routeIs('tenant.clinic-services.*')) }}" href="{{ route('tenant.clinic-services.index') }}">
                <span class="material-symbols-outlined">list_alt</span>
                <span>{{ __('Services') }}</span>
            </a>
        @endif

        @if ($staffCanManageStaff ?? false)
            <a class="{{ $navLinkClasses(request()->routeIs('tenant.staff.*')) }}" href="{{ route('tenant.staff.index') }}">
                <span class="material-symbols-outlined">badge</span>
                <span>{{ __('Staff') }}</span>
            </a>
        @endif

        @if ($staffCanViewBilling ?? false)
            <a class="{{ $navLinkClasses(request()->routeIs('tenant.invoices.*')) }}" href="{{ route('tenant.invoices.index') }}">
                <span class="material-symbols-outlined">payments</span>
                <span>{{ __('Billing') }}</span>
            </a>
        @endif
    </nav>

    <div class="mt-auto px-4 space-y-3">
        @auth('staff')
            @if ($switchableBranches->isNotEmpty())
                <div class="px-2 pb-2">
                    <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-bold">{{ __('Switch Branch') }}</p>
                    <div class="space-y-1">
                        @foreach ($switchableBranches as $branch)
                            @php $domain = $branch->domains->first(); @endphp
                            @if ($domain)
                                <a href="http://{{ $domain->domain }}:{{ request()->getPort() ?: 8000 }}/staff/login" class="block px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg truncate">
                                    {{ $branch->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="border-t border-slate-800 pt-3 space-y-1">
                <p class="px-2 text-xs text-slate-500 truncate">{{ auth('staff')->user()->full_name }}</p>

                <form method="POST" action="{{ route('tenant.logout') }}">
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
