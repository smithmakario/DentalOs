<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.design-system-head', [
        'title' => tenant('name').' · '.config('app.name', 'DentalOS'),
    ])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.tenant-navigation')

        <div class="ml-64">
            <header class="sticky top-0 z-30 flex justify-between items-center h-16 px-xl w-full bg-white/80 backdrop-blur-md border-b border-slate-200">
                <div class="flex items-center gap-lg">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm">search</span>
                        <form action="{{ ($staffCanViewPatients ?? false) ? route('tenant.patients.index') : route('tenant.appointments.index') }}" method="GET">
                            <input
                                class="pl-10 pr-4 py-2 bg-surface border-none rounded-full text-body-sm w-80 focus:ring-2 focus:ring-primary/20"
                                name="search"
                                placeholder="{{ __('Search patients, appointments...') }}"
                                type="search"
                                value="{{ request('search') }}"
                            />
                        </form>
                    </div>
                </div>
                <div class="flex items-center gap-md">
                    <button type="button" class="p-2 text-slate-500 hover:bg-slate-50 rounded-full transition-colors relative">
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
                    </button>
                    @auth('staff')
                        <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="font-label-md text-on-surface">{{ auth('staff')->user()->full_name }}</p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">
                                    {{ ucfirst(str_replace('_', ' ', auth('staff')->user()->role->value)) }}
                                </p>
                            </div>
                            <img
                                alt="{{ auth('staff')->user()->full_name }}"
                                class="w-10 h-10 rounded-full border-2 border-primary-fixed object-cover"
                                src="{{ auth('staff')->user()->avatarUrl() }}"
                            />
                        </div>
                    @endauth
                </div>
            </header>

            <main>
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
