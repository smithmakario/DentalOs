<x-app-layout>
    <div class="p-8 max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Subscriptions') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Manual Payment Bank Details') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ __('Configure bank account details shown to clinics paying via manual transfer.') }}</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8 mb-6">
            <div class="flex items-center gap-3 mb-6 p-4 bg-surface-container-low rounded-lg">
                <span class="material-symbols-outlined text-primary">payments</span>
                <div>
                    <p class="font-label-md text-on-surface">{{ __('Paystack Online Payments') }}</p>
                    <p class="text-body-sm text-outline">
                        {{ $paystackConfigured ? __('Paystack API keys are configured.') : __('Add PAYSTACK_SECRET_KEY and PAYSTACK_PUBLIC_KEY to your .env file.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('subscriptions.settings.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="bank_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Bank Name') }}</label>
                    <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $settings->bank_name) }}" required
                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none @error('bank_name') border-error @enderror" />
                    <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="account_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Account Name') }}</label>
                        <input id="account_name" name="account_name" type="text" value="{{ old('account_name', $settings->account_name) }}" required
                            class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none @error('account_name') border-error @enderror" />
                        <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="account_number" class="block font-label-md text-on-surface-variant mb-2">{{ __('Account Number') }}</label>
                        <input id="account_number" name="account_number" type="text" value="{{ old('account_number', $settings->account_number) }}" required
                            class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none @error('account_number') border-error @enderror" />
                        <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bank_code" class="block font-label-md text-on-surface-variant mb-2">{{ __('Bank Code') }}</label>
                        <input id="bank_code" name="bank_code" type="text" value="{{ old('bank_code', $settings->bank_code) }}"
                            class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                    </div>
                    <div>
                        <label for="currency" class="block font-label-md text-on-surface-variant mb-2">{{ __('Currency') }}</label>
                        <input id="currency" name="currency" type="text" value="{{ old('currency', $settings->currency ?: 'NGN') }}" required maxlength="3"
                            class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none uppercase" />
                    </div>
                </div>

                <div>
                    <label for="payment_instructions" class="block font-label-md text-on-surface-variant mb-2">{{ __('Payment Instructions') }}</label>
                    <textarea id="payment_instructions" name="payment_instructions" rows="4"
                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none">{{ old('payment_instructions', $settings->payment_instructions) }}</textarea>
                    <p class="mt-1 text-body-sm text-outline">{{ __('Shown to clinics when they choose manual payment.') }}</p>
                </div>

                <button type="submit" class="w-full py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                    {{ __('Save Bank Details') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
