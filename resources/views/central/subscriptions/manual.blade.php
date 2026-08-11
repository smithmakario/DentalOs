<x-app-layout>
    <div class="p-8 max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('subscriptions.checkout', $organization) }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Checkout') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Manual Bank Transfer') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ $organization->name }} — {{ $plan->name }} ({{ $billingCycle->value }})</p>
        </div>

        <div class="bg-primary-fixed/30 border border-primary-fixed rounded-xl p-6 mb-8">
            <p class="font-label-md text-on-surface mb-4">{{ __('Transfer exactly') }} <strong>{{ $settings->currency }} {{ number_format($amount, 2) }}</strong></p>
            <dl class="space-y-2 text-body-md">
                <div class="flex justify-between"><dt class="text-outline">{{ __('Bank') }}</dt><dd class="font-medium">{{ $settings->bank_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-outline">{{ __('Account Name') }}</dt><dd class="font-medium">{{ $settings->account_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-outline">{{ __('Account Number') }}</dt><dd class="font-mono font-medium">{{ $settings->account_number }}</dd></div>
                @if ($settings->bank_code)
                    <div class="flex justify-between"><dt class="text-outline">{{ __('Bank Code') }}</dt><dd class="font-medium">{{ $settings->bank_code }}</dd></div>
                @endif
            </dl>
            @if ($settings->payment_instructions)
                <p class="mt-4 text-body-sm text-on-surface-variant border-t border-primary-fixed pt-4">{{ $settings->payment_instructions }}</p>
            @endif
        </div>

        @if ($errors->has('manual'))
            <div class="mb-6 p-4 bg-error-container text-error rounded-lg">{{ $errors->first('manual') }}</div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
            <form method="POST" action="{{ route('subscriptions.manual.store', $organization) }}" class="space-y-6">
                @csrf
                <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}" />
                <input type="hidden" name="billing_cycle" value="{{ $billingCycle->value }}" />

                <div>
                    <label for="manual_payment_reference" class="block font-label-md text-on-surface-variant mb-2">{{ __('Payment Reference / Transaction ID') }}</label>
                    <input id="manual_payment_reference" name="manual_payment_reference" type="text" value="{{ old('manual_payment_reference') }}" required
                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none @error('manual_payment_reference') border-error @enderror" />
                    <x-input-error :messages="$errors->get('manual_payment_reference')" class="mt-2" />
                </div>

                <div>
                    <label for="manual_notes" class="block font-label-md text-on-surface-variant mb-2">{{ __('Notes (optional)') }}</label>
                    <textarea id="manual_notes" name="manual_notes" rows="3" class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none">{{ old('manual_notes') }}</textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90">
                    {{ __('Submit Payment for Verification') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
