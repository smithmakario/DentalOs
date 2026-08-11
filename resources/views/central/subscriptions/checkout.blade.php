<x-app-layout>
    <div class="p-8 max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Subscriptions') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Subscribe') }} — {{ $organization->name }}</h2>
            <p class="font-body-md text-on-surface-variant">
                {{ $organization->activeSubscription ? __('Change subscription plan for this clinic.') : __('Choose a plan and payment method.') }}
            </p>
        </div>

        @if ($errors->has('checkout'))
            <div class="mb-6 p-4 bg-error-container border border-error/20 rounded-lg text-error">{{ $errors->first('checkout') }}</div>
        @endif

        <form method="POST" action="{{ route('subscriptions.checkout.initiate', $organization) }}" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($plans as $plan)
                    <label class="cursor-pointer">
                        <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" class="peer sr-only" @checked(old('subscription_plan_id') == $plan->id || ($loop->first && ! old('subscription_plan_id'))) required />
                        <div class="bg-white border-2 border-slate-200 rounded-xl p-6 h-full peer-checked:border-primary peer-checked:shadow-lg shadow-sm transition-all">
                            <h3 class="font-h3 text-on-surface mb-2">{{ $plan->name }}</h3>
                            <p class="text-body-sm text-outline mb-4">{{ $plan->description }}</p>
                            <p class="text-2xl font-bold text-on-surface">₦{{ number_format($plan->price_monthly) }}<span class="text-sm font-normal text-outline">/mo</span></p>
                            @if ($plan->price_yearly)
                                <p class="text-body-sm text-outline mt-1">₦{{ number_format($plan->price_yearly) }}/yr</p>
                            @endif
                            @if ($plan->max_branches)
                                <p class="text-body-sm text-outline mt-3">{{ __('Up to :count branches', ['count' => $plan->max_branches]) }}</p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-h3 text-on-surface">{{ __('Billing Cycle') }}</h3>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="billing_cycle" value="monthly" @checked(old('billing_cycle', 'monthly') === 'monthly') required />
                        <span class="font-body-md">{{ __('Monthly') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="billing_cycle" value="yearly" @checked(old('billing_cycle') === 'yearly') />
                        <span class="font-body-md">{{ __('Yearly') }}</span>
                    </label>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-h3 text-on-surface">{{ __('Payment Method') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary-fixed/20">
                        <input type="radio" name="payment_method" value="paystack" @checked(old('payment_method', 'paystack') === 'paystack') @disabled(! $paystackConfigured) required />
                        <div>
                            <p class="font-label-md text-on-surface">{{ __('Paystack (Card / Bank)') }}</p>
                            <p class="text-body-sm text-outline">{{ __('Pay online instantly via Paystack.') }}</p>
                            @unless ($paystackConfigured)
                                <p class="text-body-sm text-error mt-1">{{ __('Not configured') }}</p>
                            @endunless
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary-fixed/20">
                        <input type="radio" name="payment_method" value="manual" @checked(old('payment_method') === 'manual') @disabled(! $paymentSettings?->isConfigured()) />
                        <div>
                            <p class="font-label-md text-on-surface">{{ __('Manual Bank Transfer') }}</p>
                            <p class="text-body-sm text-outline">{{ __('Transfer to platform bank account.') }}</p>
                            @unless ($paymentSettings?->isConfigured())
                                <p class="text-body-sm text-error mt-1">{{ __('Bank details not configured') }}</p>
                            @endunless
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                {{ __('Continue to Payment') }}
            </button>
        </form>
    </div>
</x-app-layout>
