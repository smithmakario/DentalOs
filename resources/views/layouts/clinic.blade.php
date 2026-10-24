<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.design-system-head', [
        'title' => ($clinicOrganization->name ?? __('Clinic')).' · '.config('app.name', 'DentalOS'),
    ])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.clinic-navigation')

        <div class="ml-64">
            <header class="sticky top-0 z-30 flex justify-between items-center h-16 px-xl w-full bg-white/80 backdrop-blur-md border-b border-slate-200">
                <div>
                    <p class="font-label-md text-on-surface">{{ __('Clinic portal') }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">
                        {{ __('All-branch overview') }}
                    </p>
                </div>
                <div class="flex items-center gap-md">
                    <x-notification-bell :items="$tenantNotifications ?? collect()" :unread-count="$tenantNotificationUnreadCount ?? 0" />
                    @auth('staff')
                        <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="font-label-md text-on-surface">{{ auth('staff')->user()->full_name }}</p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">
                                    {{ __('Primary Admin') }}
                                </p>
                            </div>
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
