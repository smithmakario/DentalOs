<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to Subscriptions') }}
                </a>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Subscription Plans') }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ __('Create and manage plans for Professional and Enterprise organizations.') }}</p>
            </div>
            <a href="{{ route('subscription-plans.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-[18px]">add</span>
                {{ __('New Plan') }}
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">{{ session('status') }}</div>
        @endif

        @foreach ($organizationTypes as $type)
            @php
                $plans = $plansByType->get($type->value, collect());
            @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-h3 text-on-surface">{{ $type->subscriptionTierLabel() }}</h3>
                        <p class="text-body-sm text-outline mt-1">
                            {{ $type === App\Enums\OrganizationType::Single
                                ? __('Plans available to single-practice organizations.')
                                : __('Plans available to multi-location enterprise organizations.') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full font-label-sm">
                        {{ trans_choice(':count plan|:count plans', $plans->count(), ['count' => $plans->count()]) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 text-on-surface-variant font-label-sm uppercase">
                                <th class="px-6 py-3">{{ __('Plan') }}</th>
                                <th class="px-6 py-3">{{ __('Monthly') }}</th>
                                <th class="px-6 py-3">{{ __('Yearly') }}</th>
                                <th class="px-6 py-3">{{ __('Branches') }}</th>
                                <th class="px-6 py-3">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($plans as $plan)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-on-surface">{{ $plan->name }}</p>
                                        @if ($plan->description)
                                            <p class="text-body-sm text-outline mt-1">{{ $plan->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">₦{{ number_format($plan->price_monthly) }}</td>
                                    <td class="px-6 py-4">
                                        {{ $plan->price_yearly ? '₦'.number_format($plan->price_yearly) : '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $plan->max_branches ? __('Up to :count', ['count' => $plan->max_branches]) : __('Unlimited') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($plan->is_active)
                                            <span class="px-2 py-1 bg-green-50 text-green-700 rounded-md text-xs font-bold">{{ __('ACTIVE') }}</span>
                                        @else
                                            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold">{{ __('INACTIVE') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('subscription-plans.edit', $plan) }}" class="text-primary font-label-md hover:underline">
                                            {{ __('Edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">
                                        {{ __('No plans yet for this organization type.') }}
                                        <a href="{{ route('subscription-plans.create') }}" class="text-primary hover:underline">{{ __('Create one') }}</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
