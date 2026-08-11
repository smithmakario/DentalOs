@php
    use App\Support\AppointmentWizard;

    $scheduledAt = Carbon\Carbon::parse($wizard['date'].' '.$wizard['time']);
@endphp

<div class="max-w-4xl mx-auto space-y-8">
    <div class="text-center">
        <h1 class="font-h1 text-on-surface">{{ __('Final Review') }}</h1>
        <p class="font-body-lg text-on-surface-variant mt-2">{{ __('Please verify your appointment details before confirming.') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div class="md:col-span-8 bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
            <h2 class="font-h3 text-on-surface mb-4">{{ __('Appointment Summary') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="flex gap-4 items-start">
                    <div class="p-2 bg-primary-fixed rounded-lg text-primary">
                        <span class="material-symbols-outlined">medical_services</span>
                    </div>
                    <div>
                        <p class="font-label-sm text-outline uppercase tracking-wider">{{ __('Service') }}</p>
                        <p class="font-h3 text-on-surface">{{ $selectedService->name }}</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="p-2 bg-secondary-fixed rounded-lg text-on-secondary-container">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div>
                        <p class="font-label-sm text-outline uppercase tracking-wider">{{ __('Doctor') }}</p>
                        <p class="font-h3 text-on-surface">{{ $selectedProvider->full_name }}</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="p-2 bg-surface-container-high rounded-lg text-on-surface">
                        <span class="material-symbols-outlined">calendar_today</span>
                    </div>
                    <div>
                        <p class="font-label-sm text-outline uppercase tracking-wider">{{ __('Date & Time') }}</p>
                        <p class="font-h3 text-on-surface">{{ $scheduledAt->format('M j, g:i A') }}</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="p-2 bg-surface-container-high rounded-lg text-on-surface">
                        <span class="material-symbols-outlined">location_on</span>
                    </div>
                    <div>
                        <p class="font-label-sm text-outline uppercase tracking-wider">{{ __('Location') }}</p>
                        <p class="font-h3 text-on-surface">{{ tenant('name') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-4 bg-primary-container rounded-xl overflow-hidden relative flex items-center justify-center min-h-[200px]">
            <div class="relative z-10 text-center p-6">
                <span class="material-symbols-outlined text-on-primary-container text-5xl mb-4 block">verified</span>
                <p class="font-label-md text-on-primary-container">{{ __('Clinical Excellence Guaranteed') }}</p>
                <p class="font-body-sm text-on-primary-container/80 mt-2">{{ \App\Support\Money::naira($selectedService->price) }} · {{ $selectedService->duration_minutes }} {{ __('min') }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('tenant.appointments.store') }}" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-6" method="POST">
        @csrf
        <input name="service_id" type="hidden" value="{{ $selectedService->id }}" />
        <input name="provider_id" type="hidden" value="{{ $selectedProvider->id }}" />
        <input name="scheduled_date" type="hidden" value="{{ $wizard['date'] }}" />
        <input name="scheduled_time" type="hidden" value="{{ $wizard['time'] }}" />

        <div>
            <label class="block font-label-md text-on-surface-variant mb-2" for="patient_id">{{ __('Patient') }} *</label>
            <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none @error('patient_id') border-error @enderror" id="patient_id" name="patient_id" required>
                <option value="">{{ __('Select patient') }}</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" @selected((int) old('patient_id', $wizard['patient_id']) === $patient->id)>{{ $patient->full_name }}</option>
                @endforeach
            </select>
            @error('patient_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block font-label-md text-on-surface-variant mb-2" for="notes">{{ __('Reason for visit / Notes for doctor (Optional)') }}</label>
            <textarea class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary transition-all font-body-md text-on-surface @error('notes') border-error @enderror" id="notes" name="notes" placeholder="{{ __('Please describe any specific concerns or symptoms...') }}" rows="3">{{ old('notes') }}</textarea>
            @error('notes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-start gap-4 bg-surface-container-low p-4 rounded-lg border border-outline-variant">
            <input class="w-5 h-5 text-primary border-outline-variant rounded focus:ring-primary mt-0.5" id="insurance_confirmed" name="insurance_confirmed" type="checkbox" value="1" @checked(old('insurance_confirmed')) />
            <div>
                <label class="font-label-md text-on-surface" for="insurance_confirmed">{{ __('Confirm Insurance Status') }}</label>
                <p class="text-on-surface-variant font-body-sm mt-1">{{ __('I confirm that insurance details on file are up to date, or I will provide them at the time of the visit.') }}</p>
            </div>
        </div>

        @error('scheduled_time')
            <p class="text-body-sm text-error">{{ $message }}</p>
        @enderror

        <div class="flex flex-col md:flex-row gap-4 justify-between items-center pt-4 border-t border-outline-variant">
            <a class="flex items-center gap-2 px-6 py-3 border border-outline-variant rounded-full text-on-surface-variant font-label-md hover:bg-surface-container-high transition-colors" href="{{ AppointmentWizard::url($wizard, 3) }}">
                <span class="material-symbols-outlined">arrow_back</span>
                {{ __('Back to Selection') }}
            </a>
            <button class="w-full md:w-auto flex items-center justify-center gap-2 px-8 py-3 bg-primary text-on-primary rounded-full font-h3 hover:opacity-90 transition-all active:scale-[0.98] shadow-md" type="submit">
                {{ __('Confirm Booking') }}
                <span class="material-symbols-outlined">event_available</span>
            </button>
        </div>
    </form>
</div>
