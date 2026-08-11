<!DOCTYPE html>
<html class="light scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Get Started')) — {{ __('DentalOs') }}</title>
    <link rel="icon" href="{{ asset('images/dentalos-logo.svg') }}" type="image/svg+xml"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0EA5E9',
                        'primary-dark': '#0284C7',
                        cta: '#FBBF24',
                        'cta-dark': '#F59E0B',
                        surface: '#F0F9FF',
                        'surface-elevated': '#FFFFFF',
                        'on-surface': '#0C4A6E',
                        'on-surface-variant': '#155E75',
                        outline: '#7DD3FC',
                    },
                    fontFamily: {
                        sans: ['Noto Sans', 'system-ui', 'sans-serif'],
                        display: ['Figtree', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 2px 8px -2px rgba(14, 165, 233, 0.08), 0 8px 24px -4px rgba(12, 74, 110, 0.06)',
                        'soft-lg': '0 4px 12px -2px rgba(14, 165, 233, 0.1), 0 16px 40px -8px rgba(12, 74, 110, 0.1)',
                    },
                },
            },
        };
    </script>
</head>
<body class="font-sans text-on-surface antialiased bg-surface min-h-screen">
    <header class="border-b border-outline/30 bg-surface-elevated/90 backdrop-blur-md">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}"><x-dentalos-logo size="md"/></a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors">{{ __('Sign in') }}</a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10 lg:py-14">
        @yield('content')
    </main>
</body>
</html>
