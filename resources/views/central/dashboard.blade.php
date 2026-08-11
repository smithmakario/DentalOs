@php
    $totalClinics = $totalBranches;
    $activeClinics = $activeBranches;
    $avatarClasses = ['bg-blue-50 text-primary', 'bg-teal-50 text-teal-600', 'bg-orange-50 text-orange-600'];
    $planForOrganization = fn (?App\Models\Organization $organization): array => match ($organization?->type) {
        App\Enums\OrganizationType::Dso => ['label' => 'Enterprise', 'class' => 'bg-primary-fixed text-primary'],
        App\Enums\OrganizationType::Single => ['label' => 'Professional', 'class' => 'bg-secondary-container text-secondary'],
        default => ['label' => 'Essential', 'class' => 'bg-surface-container text-on-surface-variant'],
    };
    $maxOnboardings = max(1, collect($onboardingChart)->max('onboardings') ?: 1);
    $formatMoney = fn ($amount): string => '₦'.number_format((float) $amount, 0);
@endphp

<x-app-layout>
    <div class="p-xl max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-xl flex justify-between items-end">
            <div>
                <h2 class="font-h1 text-on-surface">{{ __('Platform Overview') }}</h2>
                <p class="text-body-lg text-outline mt-1">
                    @if ($isSuperAdmin)
                        {{ __('Real-time health and growth metrics for DentaFlow Global.') }}
                    @else
                        {{ __('Real-time health and growth metrics for your organizations.') }}
                    @endif
                </p>
            </div>
            <div class="flex gap-md">
                @if ($isSuperAdmin)
                    <a href="{{ route('subscriptions.settings.edit') }}" class="flex items-center gap-2 px-md py-sm bg-white border border-outline-variant rounded-xl font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-sm" data-icon="account_balance">account_balance</span>
                        {{ __('Bank Details') }}
                    </a>
                @endif
                <span class="text-body-sm text-outline font-label-sm">{{ __('Last 30 days vs prior 30 days') }}</span>
                <a href="{{ route('analytics.export') }}" class="flex items-center gap-2 px-md py-sm bg-primary text-white rounded-xl font-label-md hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-sm" data-icon="download">download</span>
                    {{ __('Export Report') }}
                </a>
            </div>
        </div>

        <!-- Key Metrics Bento -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mb-xl">
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-primary-fixed rounded-lg">
                        <span class="material-symbols-outlined text-primary" data-icon="apartment">apartment</span>
                    </div>
                    @include('central.partials.trend-badge', ['trend' => $branchTrend])
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Total Clinics') }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($totalClinics) }}</h3>
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-secondary-container rounded-lg">
                        <span class="material-symbols-outlined text-secondary" data-icon="payments">payments</span>
                    </div>
                    @include('central.partials.trend-badge', ['trend' => $organizationTrend])
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Organizations') }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($totalOrganizations) }}</h3>
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-surface-container-highest rounded-lg">
                        <span class="material-symbols-outlined text-on-surface-variant" data-icon="verified_user">verified_user</span>
                    </div>
                    @include('central.partials.trend-badge', ['trend' => $activeBranchTrend])
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Active Clinics') }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($activeClinics) }}</h3>
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-tertiary-fixed rounded-lg">
                        <span class="material-symbols-outlined text-tertiary" data-icon="show_chart">show_chart</span>
                    </div>
                    @include('central.partials.trend-badge', ['trend' => $onboardingGrowthRate])
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Growth Rate') }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($onboardingGrowthRate['value'], 1) }}%</h3>
                </div>
            </div>
        </div>

        <!-- Visual Data Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg mb-xl">
            <div class="lg:col-span-2 bg-white p-xl rounded-xl border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center mb-xl">
                    <div>
                        <h3 class="font-h3 text-on-surface">{{ __('Clinic Onboarding Growth') }}</h3>
                        <p class="text-body-sm text-outline">{{ __('New organizations onboarded over the last 6 months') }}</p>
                    </div>
                </div>
                <div class="h-64 flex items-end justify-between gap-2 px-2 relative">
                    <div class="absolute inset-0 flex flex-col justify-between py-2 border-b border-slate-100">
                        <div class="w-full border-t border-slate-50 h-0"></div>
                        <div class="w-full border-t border-slate-50 h-0"></div>
                        <div class="w-full border-t border-slate-50 h-0"></div>
                        <div class="w-full border-t border-slate-50 h-0"></div>
                    </div>
                    @foreach ($onboardingChart as $month)
                        <div class="w-12 relative group flex flex-col justify-end h-full">
                            <div
                                class="w-full bg-primary rounded-t-sm"
                                style="height: {{ max(8, ($month['onboardings'] / $maxOnboardings) * 100) }}%"
                                title="{{ $month['full_label'] }}: {{ $month['onboardings'] }} {{ __('onboardings') }}"
                            ></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-md px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    @foreach ($onboardingChart as $month)
                        <span>{{ $month['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-xl rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <h3 class="font-h3 text-on-surface mb-unit">{{ __('Subscription Mix') }}</h3>
                <p class="text-body-sm text-outline mb-xl">{{ __('Active subscriptions by plan') }}</p>
                <div class="flex-1 flex flex-col justify-center items-center">
                    <div class="relative w-48 h-48 rounded-full border-[16px] border-surface-container flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-body-sm text-outline">{{ __('Active Plans') }}</p>
                            <p class="text-h2 font-bold">{{ number_format($activeSubscriptions) }}</p>
                        </div>
                    </div>
                    <div class="w-full mt-xl space-y-md">
                        @forelse ($planMix as $plan)
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ $plan['color'] }}"></span>
                                    <span class="text-body-sm text-on-surface">{{ $plan['name'] }}</span>
                                </div>
                                <span class="text-label-md font-bold">{{ number_format($plan['percentage'], 1) }}%</span>
                            </div>
                        @empty
                            <p class="text-body-sm text-outline text-center">{{ __('No active subscriptions yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Onboardings Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-xl border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h3 class="font-h3 text-on-surface">{{ __('Recent Onboardings') }}</h3>
                    <p class="text-body-sm text-outline">{{ __('Latest clinics joined the DentaFlow network.') }}</p>
                </div>
                <a href="{{ route('clinics.index') }}" class="text-primary font-label-md hover:underline">{{ __('View All Clinics') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface text-outline font-label-sm uppercase tracking-wider">
                            <th class="px-xl py-md">{{ __('Clinic Name') }}</th>
                            <th class="px-xl py-md">{{ __('Organization') }}</th>
                            <th class="px-xl py-md">{{ __('Plan') }}</th>
                            <th class="px-xl py-md">{{ __('Onboarded') }}</th>
                            <th class="px-xl py-md text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($branches->take(5) as $branch)
                            @php
                                $plan = $planForOrganization($branch->organization);
                                $initial = strtoupper(substr($branch->name, 0, 1));
                                $avatarClass = $avatarClasses[$loop->index % count($avatarClasses)];
                                $domain = $branch->domains->first();
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-xl py-md">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $avatarClass }} flex items-center justify-center font-bold text-lg">{{ $initial }}</div>
                                        <div>
                                            <p class="font-body-md font-semibold text-on-surface">{{ $branch->name }}</p>
                                            <p class="text-xs text-outline">{{ $branch->organization?->email ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-xl py-md text-body-md text-on-surface">{{ $branch->organization?->name ?? '—' }}</td>
                                <td class="px-xl py-md">
                                    <span class="px-3 py-1 {{ $plan['class'] }} rounded-full font-label-sm">{{ $plan['label'] }}</span>
                                </td>
                                <td class="px-xl py-md text-body-md text-outline">{{ $branch->created_at?->diffForHumans() ?? '—' }}</td>
                                <td class="px-xl py-md text-right">
                                    @if ($domain)
                                        <a
                                            href="http://{{ $domain->domain }}:{{ request()->getPort() ?: 8000 }}/staff/login"
                                            class="p-2 text-slate-400 hover:text-primary inline-flex"
                                            target="_blank"
                                            title="{{ __('Open clinic portal') }}"
                                        >
                                            <span class="material-symbols-outlined" data-icon="open_in_new">open_in_new</span>
                                        </a>
                                    @else
                                        <button type="button" class="p-2 text-slate-400 hover:text-primary">
                                            <span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-xl py-md text-body-md text-outline text-center">
                                    {{ __('No clinics onboarded yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
