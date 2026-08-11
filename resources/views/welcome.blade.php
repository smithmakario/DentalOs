<!DOCTYPE html>
<html class="light scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="{{ __('DentalOs — the operating system for modern dental practices. Manage clinics, branches, staff, and subscriptions from one platform.') }}"/>
    <title>{{ __('DentalOs — Dental Practice Operating System') }}</title>
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
                        'primary-light': '#38BDF8',
                        secondary: '#38BDF8',
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
                        'soft-xl': '0 8px 24px -4px rgba(14, 165, 233, 0.12), 0 24px 48px -12px rgba(12, 74, 110, 0.12)',
                    },
                },
            },
        };
    </script>
    <style>
        .hero-mesh {
            background-color: #F0F9FF;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% -20%, rgba(14, 165, 233, 0.15), transparent),
                radial-gradient(ellipse 40% 30% at 100% 0%, rgba(56, 189, 248, 0.12), transparent),
                radial-gradient(ellipse 35% 25% at 0% 50%, rgba(251, 191, 36, 0.06), transparent);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="font-sans text-on-surface antialiased bg-surface">

    {{-- Floating Navigation --}}
    <header class="fixed top-4 inset-x-4 z-50 max-w-7xl mx-auto glass-nav rounded-2xl border border-outline/30 shadow-soft">
        <div class="px-4 sm:px-6 h-16 flex items-center justify-between">
            <x-dentalos-logo size="md"/>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-on-surface-variant" aria-label="{{ __('Main navigation') }}">
                <a href="#features" class="hover:text-primary transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-md">{{ __('Features') }}</a>
                <a href="#solutions" class="hover:text-primary transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-md">{{ __('Solutions') }}</a>
                <a href="#testimonials" class="hover:text-primary transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-md">{{ __('Testimonials') }}</a>
                <a href="#platform" class="hover:text-primary transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-md">{{ __('Platform') }}</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary-dark transition-colors duration-200 shadow-soft cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        {{ __('Dashboard') }}
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-lg">
                            {{ __('Sign in') }}
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 bg-cta text-on-surface rounded-xl text-sm font-semibold hover:bg-cta-dark transition-colors duration-200 shadow-soft cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cta focus-visible:ring-offset-2">
                            {{ __('Get Started') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="hero-mesh pt-28 pb-16 lg:pt-36 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-surface-elevated/90 border border-primary/15 text-sm font-semibold text-primary-dark mb-6 shadow-soft">
                        <span class="w-2 h-2 rounded-full bg-primary shrink-0" aria-hidden="true"></span>
                        {{ __('Trusted by dental teams across Africa') }}
                    </div>

                    <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.25rem] font-extrabold tracking-tight leading-[1.08] mb-6 text-on-surface">
                        {{ __('The operating system for') }}
                        <span class="text-primary">{{ __('modern dentistry') }}</span>
                    </h1>

                    <p class="text-lg text-on-surface-variant leading-relaxed mb-8 max-w-xl">
                        {{ __('DentalOs unifies clinic management, multi-location operations, staff access, and subscription billing — so your team can focus on patient care, not paperwork.') }}
                    </p>

                    <div class="flex flex-wrap gap-3 sm:gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 sm:px-7 py-3.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors duration-200 shadow-soft-lg cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                                {{ __('Open Dashboard') }}
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 sm:px-7 py-3.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors duration-200 shadow-soft-lg cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                                {{ __('Start Free Trial') }}
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endauth
                        <a href="#features" class="inline-flex items-center gap-2 px-6 sm:px-7 py-3.5 bg-surface-elevated text-on-surface rounded-xl font-semibold border border-outline/40 hover:border-primary/40 hover:shadow-soft transition-all duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                            {{ __('Explore Features') }}
                        </a>
                    </div>

                    {{-- Social proof stats --}}
                    <dl class="grid grid-cols-3 gap-4 sm:gap-8 mt-12 pt-8 border-t border-outline/25">
                        <div>
                            <dt class="sr-only">{{ __('Uptime SLA') }}</dt>
                            <dd class="font-display text-2xl sm:text-3xl font-bold text-primary">99.9%</dd>
                            <dd class="text-xs sm:text-sm text-on-surface-variant mt-1">{{ __('Uptime SLA') }}</dd>
                        </div>
                        <div>
                            <dt class="sr-only">{{ __('Branch Support') }}</dt>
                            <dd class="font-display text-2xl sm:text-3xl font-bold text-primary">Multi</dd>
                            <dd class="text-xs sm:text-sm text-on-surface-variant mt-1">{{ __('Branch Support') }}</dd>
                        </div>
                        <div>
                            <dt class="sr-only">{{ __('Ready Architecture') }}</dt>
                            <dd class="font-display text-2xl sm:text-3xl font-bold text-primary">HIPAA</dd>
                            <dd class="text-xs sm:text-sm text-on-surface-variant mt-1">{{ __('Ready Architecture') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Dashboard preview card --}}
                <div class="relative lg:pl-4">
                    <div class="absolute -inset-3 bg-gradient-to-br from-primary/10 to-primary-light/15 rounded-3xl blur-2xl" aria-hidden="true"></div>
                    <div class="relative bg-surface-elevated rounded-2xl shadow-soft-xl border border-outline/20 p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline/20">
                            <img src="{{ asset('images/dentalos-logo.svg') }}" alt="" class="h-9 w-9 sm:h-10 sm:w-10" width="40" height="40"/>
                            <div>
                                <p class="font-display font-bold text-on-surface">DentalOs</p>
                                <p class="text-xs text-on-surface-variant">{{ __('Enterprise Admin') }}</p>
                            </div>
                            <span class="ml-auto px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200">{{ __('LIVE') }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            @foreach ([
                                ['icon' => 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402a1.125 1.125 0 0 1-1.59 1.591l-1.043-1.043M5 14.5l-1.402 1.402a1.125 1.125 0 0 0 1.59 1.591l1.043-1.043', 'value' => '24', 'label' => __('Active Clinics'), 'color' => 'text-primary'],
                                ['icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z', 'value' => '186', 'label' => __('Staff Members'), 'color' => 'text-primary-dark'],
                                ['icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z', 'value' => '₦2.4M', 'label' => __('Monthly Revenue'), 'color' => 'text-primary'],
                                ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'value' => '98%', 'label' => __('Utilization'), 'color' => 'text-primary-dark'],
                            ] as $stat)
                                <div class="p-3 sm:p-4 rounded-xl bg-surface border border-outline/15 shadow-soft">
                                    <svg class="w-5 h-5 {{ $stat['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/></svg>
                                    <p class="font-display text-xl sm:text-2xl font-bold mt-2 text-on-surface">{{ $stat['value'] }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $stat['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="h-2 bg-surface rounded-full overflow-hidden" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('Platform utilization') }}">
                            <div class="h-full w-3/4 bg-gradient-to-r from-primary to-primary-light rounded-full"></div>
                        </div>
                    </div>

                    <div class="absolute -bottom-4 -left-2 sm:-bottom-6 sm:-left-6 bg-surface-elevated rounded-xl shadow-soft-lg border border-outline/20 p-3 sm:p-4 hidden sm:block">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-on-surface">{{ __('Payment Verified') }}</p>
                                <p class="text-xs text-on-surface-variant">{{ __('Enterprise plan activated') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust bar --}}
    <section class="py-10 border-y border-outline/20 bg-surface-elevated" aria-label="{{ __('Trust indicators') }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm font-medium text-on-surface-variant mb-6">{{ __('Built for compliance-first dental operations') }}</p>
            <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-4 text-on-surface-variant">
                @foreach ([__('Tenant Isolation'), __('Role-Based Access'), __('Audit Logging'), __('Paystack Ready'), __('Multi-Branch')] as $badge)
                    <span class="inline-flex items-center gap-2 text-sm font-medium">
                        <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        {{ $badge }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Problem statement --}}
    <section class="py-20 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-primary-dark mb-3">{{ __('The challenge') }}</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight mb-6 text-on-surface">
                    {{ __('Dental practices outgrow spreadsheets and disconnected tools') }}
                </h2>
                <p class="text-lg text-on-surface-variant leading-relaxed">
                    {{ __('Managing multiple branches, staff permissions, subscriptions, and compliance across separate systems creates bottlenecks. DentalOs replaces the patchwork with one secure, scalable platform.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-20 lg:py-24 bg-surface-elevated">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <p class="text-sm font-bold uppercase tracking-widest text-primary-dark mb-3">{{ __('Platform Features') }}</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight mb-4 text-on-surface">{{ __('Everything your practice needs, connected') }}</h2>
                <p class="text-on-surface-variant text-lg">{{ __('From single-chair practices to multi-location DSOs — one platform, zero silos.') }}</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach ([
                    ['icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Z', 'title' => __('Multi-Branch Tenancy'), 'desc' => __('Each location gets its own secure workspace with centralized oversight.')],
                    ['icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z', 'title' => __('Staff & RBAC'), 'desc' => __('Role-based access across branches with provisioning in one click.')],
                    ['icon' => 'M2.25 8.25h19.5M2.25 8.25a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25M2.25 8.25v8.25A2.25 2.25 0 0 0 4.5 18.75h15a2.25 2.25 0 0 0 2.25-2.25V8.25', 'title' => __('Subscriptions & Billing'), 'desc' => __('Flexible plans for single practices and enterprise networks with Paystack support.')],
                    ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'title' => __('Analytics Dashboard'), 'desc' => __('Real-time visibility into clinic performance and platform health.')],
                    ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'title' => __('Audit Logs'), 'desc' => __('Full traceability for compliance and operational accountability.')],
                    ['icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z', 'title' => __('Enterprise Security'), 'desc' => __('Tenant isolation, encrypted sessions, and platform-level access controls.')],
                ] as $feature)
                    <article class="group p-6 rounded-2xl border border-outline/20 bg-surface hover:border-primary/30 hover:shadow-soft-lg transition-all duration-200 cursor-default">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary transition-colors duration-200">
                            <svg class="w-6 h-6 text-primary group-hover:text-white transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                        </div>
                        <h3 class="font-display text-lg font-bold mb-2 text-on-surface">{{ $feature['title'] }}</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Solutions --}}
    <section id="solutions" class="py-20 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <p class="text-sm font-bold uppercase tracking-widest text-primary-dark mb-3">{{ __('Solutions') }}</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight mb-4 text-on-surface">{{ __('Built for how you practice') }}</h2>
            </div>

            <div class="grid md:grid-cols-2 gap-6 lg:gap-8">
                <article class="relative overflow-hidden rounded-2xl bg-surface-elevated border border-outline/20 p-8 shadow-soft hover:shadow-soft-lg transition-shadow duration-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-light/15 rounded-full -translate-y-1/2 translate-x-1/2" aria-hidden="true"></div>
                    <svg class="w-8 h-8 text-primary mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9.75a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3m-6 0h6"/></svg>
                    <h3 class="font-display text-2xl font-bold mb-1 text-on-surface">{{ __('Professional') }}</h3>
                    <p class="text-sm font-medium text-on-surface-variant mb-4">{{ __('Single Practice') }}</p>
                    <p class="text-on-surface-variant mb-6 leading-relaxed">{{ __('Ideal for independent clinics with one primary location. Streamlined onboarding, essential plans, and focused workflows.') }}</p>
                    <ul class="space-y-2.5 text-sm text-on-surface-variant">
                        @foreach ([__('Single-location management'), __('Staff provisioning'), __('Flexible subscription plans')] as $item)
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </article>

                <article class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-dark p-8 text-white shadow-soft-xl">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2" aria-hidden="true"></div>
                    <span class="inline-block px-3 py-1 rounded-full bg-cta/90 text-on-surface text-xs font-bold mb-4">{{ __('Most Popular') }}</span>
                    <svg class="w-8 h-8 text-white/90 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Z"/></svg>
                    <h3 class="font-display text-2xl font-bold mb-1">{{ __('Enterprise') }}</h3>
                    <p class="text-sm font-medium text-white/75 mb-4">{{ __('Multi-location / DSO') }}</p>
                    <p class="text-white/90 mb-6 leading-relaxed">{{ __('Built for dental service organizations managing multiple branches, centralized billing, and network-wide auditing.') }}</p>
                    <ul class="space-y-2.5 text-sm text-white/90">
                        @foreach ([__('Unlimited branch scaling'), __('Centralized admin console'), __('Enterprise subscription tiers')] as $item)
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-cta shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section id="testimonials" class="py-20 lg:py-24 bg-surface-elevated">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <p class="text-sm font-bold uppercase tracking-widest text-primary-dark mb-3">{{ __('Social Proof') }}</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight mb-4 text-on-surface">{{ __('Trusted by practice leaders') }}</h2>
                <p class="text-on-surface-variant text-lg">{{ __('See how dental teams use DentalOs to simplify operations and scale with confidence.') }}</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach ([
                    ['quote' => __('We went from juggling three systems to one dashboard. Onboarding a new branch now takes hours, not weeks.'), 'name' => 'Dr. Amara Okafor', 'role' => __('Clinical Director'), 'org' => __('SmileLine Dental Group'), 'initials' => 'AO'],
                    ['quote' => __('The RBAC and audit logs gave our compliance team exactly what they needed. Enterprise billing through Paystack just works.'), 'name' => 'James Adeyemi', 'role' => __('Operations Manager'), 'org' => __('Pearl Dental Network'), 'initials' => 'JA'],
                    ['quote' => __('As a single-practice owner, I needed something simple but scalable. DentalOs grew with us when we opened our second location.'), 'name' => 'Dr. Chioma Eze', 'role' => __('Practice Owner'), 'org' => __('BrightSmile Clinic'), 'initials' => 'CE'],
                ] as $testimonial)
                    <blockquote class="flex flex-col p-6 rounded-2xl bg-surface border border-outline/20 shadow-soft hover:shadow-soft-lg transition-shadow duration-200">
                        <div class="flex gap-0.5 mb-4" aria-label="{{ __('5 out of 5 stars') }}">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 text-cta" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                            @endfor
                        </div>
                        <p class="text-on-surface-variant text-sm leading-relaxed flex-1 italic">&ldquo;{{ $testimonial['quote'] }}&rdquo;</p>
                        <footer class="flex items-center gap-3 mt-6 pt-5 border-t border-outline/20">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0" aria-hidden="true">
                                <span class="text-sm font-bold text-primary-dark">{{ $testimonial['initials'] }}</span>
                            </div>
                            <div>
                                <cite class="not-italic text-sm font-semibold text-on-surface">{{ $testimonial['name'] }}</cite>
                                <p class="text-xs text-on-surface-variant">{{ $testimonial['role'] }}, {{ $testimonial['org'] }}</p>
                            </div>
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Platform --}}
    <section id="platform" class="py-20 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <div class="relative max-w-md mx-auto">
                        <div class="absolute -inset-4 bg-gradient-to-br from-primary/10 to-cta/10 rounded-3xl blur-2xl" aria-hidden="true"></div>
                        <img src="{{ asset('images/dentalos-logo.png') }}" alt="{{ __('DentalOs platform') }}" class="relative w-full rounded-2xl shadow-soft-xl border border-outline/20" width="480" height="480"/>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <p class="text-sm font-bold uppercase tracking-widest text-primary-dark mb-3">{{ __('Why DentalOs') }}</p>
                    <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight mb-8 text-on-surface">{{ __('One platform. Every location. Total control.') }}</h2>
                    <div class="space-y-6">
                        @foreach ([
                            ['num' => '01', 'title' => __('Onboard in minutes'), 'desc' => __('Spin up clinics with branch domains, admin accounts, and subscription plans from a guided wizard.')],
                            ['num' => '02', 'title' => __('Pay your way'), 'desc' => __('Accept Paystack online payments or manual bank transfers with super-admin verification.')],
                            ['num' => '03', 'title' => __('Scale without friction'), 'desc' => __('Add branches, assign staff, and upgrade plans as your organization grows.')],
                        ] as $step)
                            <div class="flex gap-5">
                                <span class="font-display text-3xl font-black text-primary/20 shrink-0 tabular-nums">{{ $step['num'] }}</span>
                                <div>
                                    <h3 class="font-display font-bold text-lg mb-1 text-on-surface">{{ $step['title'] }}</h3>
                                    <p class="text-on-surface-variant text-sm leading-relaxed">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary via-primary-dark to-on-surface px-6 py-14 sm:px-12 sm:py-16 text-center text-white shadow-soft-xl">
                <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;" aria-hidden="true"></div>
                <div class="relative">
                    <h2 class="font-display text-3xl sm:text-4xl font-bold mb-4">{{ __('Ready to modernize your practice?') }}</h2>
                    <p class="text-white/85 text-lg mb-8 max-w-xl mx-auto">{{ __('Join dental teams using DentalOs to run smarter, scale faster, and deliver better patient experiences.') }}</p>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-cta text-on-surface rounded-xl font-bold hover:bg-cta-dark transition-colors duration-200 shadow-soft-lg cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cta focus-visible:ring-offset-2 focus-visible:ring-offset-primary">
                            {{ __('Go to Dashboard') }}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-cta text-on-surface rounded-xl font-bold hover:bg-cta-dark transition-colors duration-200 shadow-soft-lg cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cta focus-visible:ring-offset-2 focus-visible:ring-offset-primary">
                            {{ __('Sign in to DentalOs') }}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-on-surface text-white/75 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                <div>
                    <x-dentalos-logo size="md" class="[&_span]:text-white [&_span_span]:text-primary-light"/>
                    <p class="text-sm mt-3 max-w-xs text-white/70">{{ __('Clinical precision. Enterprise scale. One operating system.') }}</p>
                </div>
                <nav class="flex flex-wrap gap-6 text-sm" aria-label="{{ __('Footer navigation') }}">
                    <a href="#features" class="hover:text-white transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-light focus-visible:ring-offset-2 focus-visible:ring-offset-on-surface rounded-md">{{ __('Features') }}</a>
                    <a href="#solutions" class="hover:text-white transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-light focus-visible:ring-offset-2 focus-visible:ring-offset-on-surface rounded-md">{{ __('Solutions') }}</a>
                    <a href="#testimonials" class="hover:text-white transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-light focus-visible:ring-offset-2 focus-visible:ring-offset-on-surface rounded-md">{{ __('Testimonials') }}</a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="hover:text-white transition-colors duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-light focus-visible:ring-offset-2 focus-visible:ring-offset-on-surface rounded-md">{{ __('Sign in') }}</a>
                    @endif
                </nav>
            </div>
            <div class="mt-10 pt-8 border-t border-white/10 text-sm text-center md:text-left text-white/60">
                © {{ date('Y') }} DentalOs. {{ __('All rights reserved.') }}
            </div>
        </div>
    </footer>

</body>
</html>
