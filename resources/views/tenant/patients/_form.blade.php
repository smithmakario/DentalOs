@php
    $inputClass = 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none';
    $selectedPaymentMethod = old('preferred_payment_method', $patient->preferred_payment_method?->value);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="first_name">{{ __('First Name') }} *</label>
        <input class="{{ $inputClass }} @error('first_name') border-error @enderror" id="first_name" name="first_name" type="text" value="{{ old('first_name', $patient->first_name) }}" required />
        @error('first_name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="last_name">{{ __('Last Name') }} *</label>
        <input class="{{ $inputClass }} @error('last_name') border-error @enderror" id="last_name" name="last_name" type="text" value="{{ old('last_name', $patient->last_name) }}" required />
        @error('last_name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="email">{{ __('Email') }}</label>
        <input class="{{ $inputClass }} @error('email') border-error @enderror" id="email" name="email" type="email" value="{{ old('email', $patient->email) }}" />
        @error('email') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="phone">{{ __('Phone') }}</label>
        <input class="{{ $inputClass }} @error('phone') border-error @enderror" id="phone" name="phone" type="text" value="{{ old('phone', $patient->phone) }}" />
        @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="date_of_birth">{{ __('Date of Birth') }}</label>
        <input class="{{ $inputClass }} @error('date_of_birth') border-error @enderror" id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" />
        @error('date_of_birth') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="gender">{{ __('Gender') }}</label>
        <select class="{{ $inputClass }} @error('gender') border-error @enderror" id="gender" name="gender">
            <option value="">{{ __('Select gender') }}</option>
            @foreach (['male' => __('Male'), 'female' => __('Female'), 'other' => __('Other'), 'prefer_not_to_say' => __('Prefer not to say')] as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $patient->gender) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('gender') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="address">{{ __('Address') }}</label>
        <textarea class="{{ $inputClass }} @error('address') border-error @enderror" id="address" name="address" rows="2">{{ old('address', $patient->address) }}</textarea>
        @error('address') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="emergency_contact_name">{{ __('Emergency Contact') }}</label>
        <input class="{{ $inputClass }}" id="emergency_contact_name" name="emergency_contact_name" type="text" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" />
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="emergency_contact_phone">{{ __('Emergency Phone') }}</label>
        <input class="{{ $inputClass }}" id="emergency_contact_phone" name="emergency_contact_phone" type="text" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" />
    </div>
</div>

<div class="mt-8 border-t border-slate-100 pt-8">
    <h3 class="font-h3 text-on-surface mb-2">{{ __('Payment Preference') }}</h3>
    <p class="text-body-sm text-on-surface-variant mb-4">{{ __('How does this patient typically pay for treatment?') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
        @foreach (\App\Enums\PaymentMethod::cases() as $method)
            <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition-colors {{ $selectedPaymentMethod === $method->value ? 'border-primary bg-primary/5 ring-1 ring-primary/20' : 'border-slate-200 hover:border-slate-300' }}">
                <input
                    class="text-primary focus:ring-primary payment-method-option"
                    name="preferred_payment_method"
                    type="radio"
                    value="{{ $method->value }}"
                    @checked($selectedPaymentMethod === $method->value)
                    required
                />
                <span class="font-label-md text-on-surface">{{ $method->label() }}</span>
            </label>
        @endforeach
    </div>
    @error('preferred_payment_method') <p class="mb-4 text-body-sm text-error">{{ $message }}</p> @enderror

    <div id="hmo-details" class="grid grid-cols-1 md:grid-cols-2 gap-6 {{ $selectedPaymentMethod === \App\Enums\PaymentMethod::Hmo->value ? '' : 'hidden' }}">
        <div class="md:col-span-2">
            <p class="text-body-sm text-on-surface-variant mb-4">{{ __('Enter the patient\'s HMO / health plan details.') }}</p>
        </div>
        <div>
            <label class="block font-label-md text-on-surface-variant mb-2" for="insurance_provider">{{ __('HMO Provider') }} *</label>
            <input class="{{ $inputClass }} @error('insurance_provider') border-error @enderror" id="insurance_provider" name="insurance_provider" type="text" value="{{ old('insurance_provider', $patient->insurance_provider) }}" placeholder="{{ __('e.g. Reliance HMO, Hygeia') }}" />
            @error('insurance_provider') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block font-label-md text-on-surface-variant mb-2" for="hmo_plan">{{ __('Plan / Scheme') }} *</label>
            <input class="{{ $inputClass }} @error('hmo_plan') border-error @enderror" id="hmo_plan" name="hmo_plan" type="text" value="{{ old('hmo_plan', $patient->hmo_plan) }}" placeholder="{{ __('e.g. Corporate Plan, Retail Plan') }}" />
            @error('hmo_plan') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="block font-label-md text-on-surface-variant mb-2" for="insurance_number">{{ __('Member / Enrollee ID') }} *</label>
            <input class="{{ $inputClass }} @error('insurance_number') border-error @enderror" id="insurance_number" name="insurance_number" type="text" value="{{ old('insurance_number', $patient->insurance_number) }}" />
            @error('insurance_number') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 gap-6">
    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="medical_notes">{{ __('Medical Notes') }}</label>
        <textarea class="{{ $inputClass }}" id="medical_notes" name="medical_notes" rows="4">{{ old('medical_notes', $patient->medical_notes) }}</textarea>
    </div>

    <div>
        <label class="inline-flex items-center gap-2">
            <input
                class="rounded border-outline-variant text-primary focus:ring-primary"
                name="is_active"
                type="checkbox"
                value="1"
                @checked(old('is_active', $patient->is_active))
            />
            <span class="text-body-md text-on-surface">{{ __('Active patient record') }}</span>
        </label>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hmoDetails = document.getElementById('hmo-details');
    const hmoValue = @json(\App\Enums\PaymentMethod::Hmo->value);
    const options = document.querySelectorAll('.payment-method-option');

    const toggleHmoDetails = () => {
        const selected = document.querySelector('.payment-method-option:checked');
        const showHmo = selected?.value === hmoValue;
        hmoDetails?.classList.toggle('hidden', !showHmo);

        hmoDetails?.querySelectorAll('input').forEach((input) => {
            input.required = showHmo;
        });
    };

    options.forEach((option) => option.addEventListener('change', toggleHmoDetails));
    toggleHmoDetails();
});
</script>
