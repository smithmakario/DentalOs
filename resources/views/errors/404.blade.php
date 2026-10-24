<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('Page not found') }} · {{ config('app.name', 'DentalOs') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 font-[Inter,system-ui,sans-serif]">
    @php
        $message = trim((string) ($exception?->getMessage() ?? ''));
        $defaultMessage = __('The page you are looking for does not exist or is no longer available.');
        $isTenantMessage = $message !== '' && (
            str_contains(strtolower($message), 'clinic')
            || str_contains(strtolower($message), 'branch')
            || str_contains(strtolower($message), 'subscription')
        );
        $title = $isTenantMessage ? __('Branch unavailable') : __('Page not found');
        $body = $isTenantMessage ? $message : ($message !== '' ? $message : $defaultMessage);
    @endphp

    <div class="min-h-screen flex items-center justify-center px-6 py-16">
        <div class="w-full max-w-lg text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                <span class="material-symbols-outlined text-[32px]">travel_explore</span>
            </div>
            <p class="text-sm font-semibold tracking-wide text-blue-700 uppercase mb-2">404</p>
            <h1 class="text-3xl font-bold tracking-tight mb-3">{{ $title }}</h1>
            <p class="text-slate-600 leading-relaxed mb-8">{{ $body }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    {{ __('Go to home') }}
                </a>
                @if ($isTenantMessage && Route::has('clinic.login'))
                    <a href="{{ route('clinic.login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        {{ __('Clinic login') }}
                    </a>
                @elseif (Route::has('login'))
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        {{ __('Platform login') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
