<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Subscriptions & Billing') }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ __('Manage clinic plans, Paystack payments, and manual bank transfers.') }}</p>
            </div>
            @if ($isSuperAdmin)
                <div class="flex items-center gap-3">
                    <a href="{{ route('subscription-plans.index') }}" class="flex items-center gap-2 bg-white border border-outline-variant text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                        {{ __('Manage Plans') }}
                    </a>
                    <a href="{{ route('subscriptions.settings.edit') }}" class="flex items-center gap-2 bg-white border border-outline-variant text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-[18px]">account_balance</span>
                        {{ __('Bank Details') }}
                    </a>
                </div>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-error-container border border-error/20 rounded-lg text-body-md text-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-outline mb-1">{{ __('Payment Methods') }}</p>
                <div class="space-y-2 mt-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $paystackConfigured ? 'bg-green-500' : 'bg-slate-300' }}"></span>
                        <span class="font-body-md text-on-surface">{{ __('Paystack (Online)') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $paymentSettings?->isConfigured() ? 'bg-green-500' : 'bg-slate-300' }}"></span>
                        <span class="font-body-md text-on-surface">{{ __('Manual Bank Transfer') }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm md:col-span-2">
                <p class="text-body-sm text-outline mb-1">{{ __('Available Plans') }}</p>
                <div class="flex flex-wrap gap-3 mt-3">
                    @foreach ($plans as $plan)
                        <span class="px-3 py-1 bg-primary-fixed text-primary rounded-full font-label-sm" title="{{ $plan->organization_type?->subscriptionTierLabel() }}">
                            {{ $plan->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($isSuperAdmin && $pendingPayments->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-h3 text-on-surface">{{ __('Pending Manual Verifications') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 text-on-surface-variant font-label-sm uppercase">
                                <th class="px-6 py-3">{{ __('Clinic') }}</th>
                                <th class="px-6 py-3">{{ __('Plan') }}</th>
                                <th class="px-6 py-3">{{ __('Amount') }}</th>
                                <th class="px-6 py-3">{{ __('Reference') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pendingPayments as $payment)
                                <tr>
                                    <td class="px-6 py-4">{{ $payment->organization->name }}</td>
                                    <td class="px-6 py-4">{{ $payment->subscription?->plan?->name }}</td>
                                    <td class="px-6 py-4">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-6 py-4 font-mono text-sm">{{ $payment->manual_payment_reference }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="{{ route('subscriptions.payments.verify', $payment) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90">
                                                {{ __('Verify') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-h3 text-on-surface">{{ __('Clinic Subscriptions') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-on-surface-variant font-label-sm uppercase">
                            <th class="px-6 py-3">{{ __('Clinic') }}</th>
                            <th class="px-6 py-3">{{ __('Current Plan') }}</th>
                            <th class="px-6 py-3">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($organizations as $organization)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-on-surface">{{ $organization->name }}</td>
                                <td class="px-6 py-4">{{ $organization->activeSubscription?->plan?->name ?? __('None') }}</td>
                                <td class="px-6 py-4">
                                    @if ($organization->activeSubscription)
                                        <span class="px-2 py-1 bg-green-50 text-green-700 rounded-md text-xs font-bold">{{ __('ACTIVE') }}</span>
                                    @else
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold">{{ __('NO PLAN') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('update', $organization)
                                        <a href="{{ route('subscriptions.checkout', $organization) }}" class="text-primary font-label-md hover:underline">
                                            {{ $organization->activeSubscription ? __('Change Plan') : __('Subscribe') }}
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-on-surface-variant">{{ __('No clinics available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-h3 text-on-surface">{{ __('Recent Payments') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-on-surface-variant font-label-sm uppercase">
                            <th class="px-6 py-3">{{ __('Clinic') }}</th>
                            <th class="px-6 py-3">{{ __('Method') }}</th>
                            <th class="px-6 py-3">{{ __('Amount') }}</th>
                            <th class="px-6 py-3">{{ __('Status') }}</th>
                            <th class="px-6 py-3">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="px-6 py-4">{{ $payment->organization->name }}</td>
                                <td class="px-6 py-4 capitalize">{{ $payment->payment_method->value }}</td>
                                <td class="px-6 py-4">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $payment->status->value) }}</td>
                                <td class="px-6 py-4 text-outline">{{ $payment->created_at?->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">{{ __('No payments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
