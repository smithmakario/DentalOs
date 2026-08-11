@php
    $inputClass = 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="patient_id">{{ __('Patient') }} *</label>
        <select class="{{ $inputClass }} @error('patient_id') border-error @enderror" id="patient_id" name="patient_id" required>
            <option value="">{{ __('Select patient') }}</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((int) old('patient_id', $appointment->patient_id) === $patient->id)>
                    {{ $patient->full_name }}
                </option>
            @endforeach
        </select>
        @error('patient_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="provider_id">{{ __('Provider') }} *</label>
        <select class="{{ $inputClass }} @error('provider_id') border-error @enderror" id="provider_id" name="provider_id" required>
            <option value="">{{ __('Select provider') }}</option>
            @foreach ($providers as $provider)
                <option value="{{ $provider->id }}" @selected((int) old('provider_id', $appointment->provider_id) === $provider->id)>
                    {{ $provider->full_name }} ({{ ucfirst(str_replace('_', ' ', $provider->role->value)) }})
                </option>
            @endforeach
        </select>
        @error('provider_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="title">{{ __('Title / Reason') }}</label>
        <input class="{{ $inputClass }}" id="title" name="title" type="text" value="{{ old('title', $appointment->title) }}" placeholder="{{ __('e.g. Routine cleaning, Root canal consult') }}" />
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="duration_minutes">{{ __('Duration') }} *</label>
        <select class="{{ $inputClass }} @error('duration_minutes') border-error @enderror" id="duration_minutes" name="duration_minutes" required>
            @foreach ([15, 30, 45, 60, 90] as $minutes)
                <option value="{{ $minutes }}" @selected((int) old('duration_minutes', $appointment->duration_minutes) === $minutes)>
                    {{ $minutes }} {{ __('minutes') }}
                </option>
            @endforeach
        </select>
        @error('duration_minutes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="scheduled_at">{{ __('Date & Time') }} *</label>
        <input
            class="{{ $inputClass }} @error('scheduled_at') border-error @enderror"
            id="scheduled_at"
            name="scheduled_at"
            type="datetime-local"
            value="{{ old('scheduled_at', $appointment->scheduled_at?->format('Y-m-d\TH:i')) }}"
            required
        />
        @error('scheduled_at') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="status">{{ __('Status') }} *</label>
        <select class="{{ $inputClass }} @error('status') border-error @enderror" id="status" name="status" required>
            @foreach (App\Enums\AppointmentStatus::cases() as $statusOption)
                <option value="{{ $statusOption->value }}" @selected(old('status', $appointment->status?->value) === $statusOption->value)>
                    {{ ucfirst(str_replace('_', ' ', $statusOption->value)) }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="notes">{{ __('Notes') }}</label>
        <textarea class="{{ $inputClass }}" id="notes" name="notes" rows="4">{{ old('notes', $appointment->notes) }}</textarea>
    </div>
</div>
