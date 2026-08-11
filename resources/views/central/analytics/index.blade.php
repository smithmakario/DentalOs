@php
    $maxRevenue = max(1, collect($monthlyTrends)->max('revenue') ?: 1);
    $maxOnboardings = max(1, collect($monthlyTrends)->max('onboardings') ?: 1);
    $formatMoney = fn ($amount): string => '₦'.number_format((float) $amount, 0);
@endphp

<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Analytics') }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    @if ($isSuperAdmin)
                        {{ __('Platform-wide performance, revenue, and growth insights.') }}
                    @else
                        {{ __('Performance and growth insights for your organizations.') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('analytics.export') }}" class="flex items-center gap-2 bg-white border border-outline-variant text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-[18px]">download</span>
                {{ __('Export CSV') }}
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-outline mb-1">{{ __('Total Revenue') }}</p>
                <p class="text-2xl font-bold text-on-surface">{{ $formatMoney($totalRevenue) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-outline mb-1">{{ __('Revenue (30 Days)') }}</p>
                <p class="text-2xl font-bold text-on-surface">{{ $formatMoney($revenueLast30Days) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-outline mb-1">{{ __('Active Subscriptions') }}</p>
                <p class="text-2xl font-bold text-on-surface">{{ number_format($activeSubscriptions) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-outline mb-1">{{ __('Pending Payments') }}</p>
                <p class="text-2xl font-bold text-on-surface">{{ number_format($pendingPayments) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Organizations') }}</p>
                <p class="text-2xl font-bold text-on-surface mt-2">{{ number_format($totalOrganizations) }}</p>
                <p class="text-body-sm text-on-surface-variant mt-1">{{ __(':count active', ['count' => $activeOrganizations]) }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Branches') }}</p>
                <p class="text-2xl font-bold text-on-surface mt-2">{{ number_format($totalBranches) }}</p>
                <p class="text-body-sm text-on-surface-variant mt-1">{{ __(':count active', ['count' => $activeBranches]) }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Staff Members') }}</p>
                <p class="text-2xl font-bold text-on-surface mt-2">{{ number_format($totalStaff) }}</p>
                <p class="text-body-sm text-on-surface-variant mt-1">{{ __(':count active', ['count' => $activeStaff]) }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Failed Payments') }}</p>
                <p class="text-2xl font-bold text-on-surface mt-2">{{ number_format($failedPayments) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-1">{{ __('Revenue Trend') }}</h3>
                <p class="text-body-sm text-outline mb-6">{{ __('Completed subscription payments over the last 6 months.') }}</p>
                <div class="flex items-end justify-between gap-3 h-48">
                    @foreach ($monthlyTrends as $month)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-slate-100 rounded-t-lg flex items-end justify-center" style="height: 10rem;">
                                <div
                                    class="w-full max-w-[2.5rem] bg-primary rounded-t-lg transition-all"
                                    style="height: {{ max(4, ($month['revenue'] / $maxRevenue) * 100) }}%"
                                    title="{{ $month['full_label'] }}: {{ $formatMoney($month['revenue']) }}"
                                ></div>
                            </div>
                            <span class="text-xs text-outline font-label-sm">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-1">{{ __('Clinic Onboarding') }}</h3>
                <p class="text-body-sm text-outline mb-6">{{ __('New organizations onboarded per month.') }}</p>
                <div class="flex items-end justify-between gap-3 h-48">
                    @foreach ($monthlyTrends as $month)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-slate-100 rounded-t-lg flex items-end justify-center" style="height: 10rem;">
                                <div
                                    class="w-full max-w-[2.5rem] bg-secondary rounded-t-lg transition-all"
                                    style="height: {{ max(4, ($month['onboardings'] / $maxOnboardings) * 100) }}%"
                                    title="{{ $month['full_label'] }}: {{ $month['onboardings'] }}"
                                ></div>
                            </div>
                            <span class="text-xs text-outline font-label-sm">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Active Plans') }}</h3>
                @forelse ($planDistribution as $planName => $count)
                    <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
                        <span class="font-body-md text-on-surface">{{ $planName }}</span>
                        <span class="px-3 py-1 bg-primary-fixed text-primary rounded-full font-label-sm">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-body-md text-outline">{{ __('No active subscriptions yet.') }}</p>
                @endforelse
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Payment Methods') }}</h3>
                @forelse ($paymentMethodBreakdown as $method => $count)
                    <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
                        <span class="font-body-md text-on-surface">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                        <span class="px-3 py-1 bg-surface-container text-on-surface-variant rounded-full font-label-sm">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-body-md text-outline">{{ __('No completed payments yet.') }}</p>
                @endforelse

                @if ($recentOnboardings->isNotEmpty())
                    <h3 class="font-h3 text-on-surface mt-8 mb-4">{{ __('Recent Onboardings') }}</h3>
                    <div class="space-y-3">
                        @foreach ($recentOnboardings as $organization)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-body-md text-on-surface">{{ $organization->name }}</p>
                                    <p class="text-body-sm text-outline">{{ $organization->created_at->format('M j, Y') }}</p>
                                </div>
                                @if ($organization->is_active)
                                    <span class="text-xs font-bold text-green-700 bg-green-50 px-2 py-1 rounded">{{ __('ACTIVE') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
