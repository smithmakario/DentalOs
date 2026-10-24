@php
    $statusActions = [
        App\Enums\AppointmentStatus::Confirmed->value => __('Confirm'),
        App\Enums\AppointmentStatus::CheckedIn->value => __('Check In'),
        App\Enums\AppointmentStatus::InProgress->value => __('Start'),
        App\Enums\AppointmentStatus::Completed->value => __('Complete'),
        App\Enums\AppointmentStatus::Cancelled->value => __('Cancel'),
        App\Enums\AppointmentStatus::NoShow->value => __('No Show'),
    ];
@endphp

<x-tenant-layout>
    <div class="p-8 max-w-5xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-start mb-8">
            <div>
                <a class="inline-flex items-center gap-1 text-body-md text-primary hover:underline mb-4" href="{{ route('tenant.appointments.index') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to appointments') }}
                </a>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">
                    {{ $appointment->title ?: __('Appointment') }}
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ $appointment->scheduled_at->format('l, F j, Y · g:i A') }}
                    · {{ $appointment->duration_minutes }} {{ __('minutes') }}
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap justify-end">
                @include('tenant.appointments._status-badge', ['status' => $appointment->status])
                @can('create', App\Models\Invoice::class)
                    @if ($appointment->invoice)
                        <a class="flex items-center gap-2 border border-slate-200 text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-slate-50 transition-colors" href="{{ route('tenant.invoices.show', $appointment->invoice) }}">
                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                            {{ __('View Invoice') }}
                        </a>
                    @else
                        <a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity" href="{{ route('tenant.invoices.create', ['appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id]) }}">
                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                            {{ __('Raise Invoice') }}
                        </a>
                    @endif
                @endcan
                @can('update', $appointment)
                    <a class="flex items-center gap-2 border border-slate-200 text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-slate-50 transition-colors" href="{{ route('tenant.appointments.edit', $appointment) }}">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        {{ __('Edit') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Patient') }}</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Name') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">
                            <a class="text-primary hover:underline" href="{{ route('tenant.patients.show', $appointment->patient) }}">
                                {{ $appointment->patient->full_name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Phone') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $appointment->patient->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Email') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $appointment->patient->email ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Provider') }}</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Name') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $appointment->provider->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Role') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ ucfirst(str_replace('_', ' ', $appointment->provider->role->value)) }}</dd>
                    </div>
                    @if ($appointment->provider->specialization)
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Specialization') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $appointment->provider->specialization }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        @if ($appointment->notes)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-8">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Scheduling Notes') }}</h3>
                <p class="text-body-md text-on-surface whitespace-pre-line">{{ $appointment->notes }}</p>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-8">
            <div class="mb-4">
                <h3 class="font-h3 text-on-surface">{{ __('SOAP Clinical Notes') }}</h3>
                <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Subjective, Objective, Assessment, Plan — documented per visit.') }}</p>
            </div>

            @can('updateClinicalNotes', $appointment)
                <form method="POST" action="{{ route('tenant.appointments.clinical-notes', $appointment) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-1" for="soap_subjective">{{ __('Subjective') }}</label>
                        <textarea id="soap_subjective" name="soap_subjective" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-body-md" placeholder="{{ __('Patient-reported symptoms and concerns') }}">{{ old('soap_subjective', $appointment->soap_subjective) }}</textarea>
                        @error('soap_subjective') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-1" for="soap_objective">{{ __('Objective / Aim') }}</label>
                        <textarea id="soap_objective" name="soap_objective" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-body-md" placeholder="{{ __('Clinical findings, exam notes') }}">{{ old('soap_objective', $appointment->soap_objective) }}</textarea>
                        @error('soap_objective') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-1" for="soap_assessment">{{ __('Assessment') }}</label>
                        <textarea id="soap_assessment" name="soap_assessment" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-body-md" placeholder="{{ __('Diagnosis / clinical impression') }}">{{ old('soap_assessment', $appointment->soap_assessment) }}</textarea>
                        @error('soap_assessment') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-1" for="soap_plan">{{ __('Plan') }}</label>
                        <textarea id="soap_plan" name="soap_plan" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-body-md" placeholder="{{ __('Treatment steps, follow-up, prescriptions') }}">{{ old('soap_plan', $appointment->soap_plan) }}</textarea>
                        @error('soap_plan') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        {{ __('Save clinical notes') }}
                    </button>
                </form>
            @else
                @if ($appointment->hasSoapNotes())
                    <dl class="space-y-4">
                        @foreach ([
                            'soap_subjective' => __('Subjective'),
                            'soap_objective' => __('Objective / Aim'),
                            'soap_assessment' => __('Assessment'),
                            'soap_plan' => __('Plan'),
                        ] as $field => $label)
                            @if (filled($appointment->{$field}))
                                <div>
                                    <dt class="text-label-sm text-on-surface-variant uppercase">{{ $label }}</dt>
                                    <dd class="text-body-md text-on-surface mt-1 whitespace-pre-line">{{ $appointment->{$field} }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                @else
                    <p class="text-body-md text-on-surface-variant">{{ __('No clinical notes recorded for this visit yet.') }}</p>
                @endif
            @endcan
        </div>

        @can('updateStatus', $appointment)
            @php
                $dayOfUnlocked = $appointment->isOnOrAfterScheduledDay();
                $needsDayGate = ! $dayOfUnlocked;
            @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-2">{{ __('Update Status') }}</h3>
                @if ($needsDayGate)
                    <p class="text-body-sm text-on-surface-variant mb-4">
                        {{ __('Check-in and clinical statuses unlock on :date. Confirm or cancel anytime. Use emergency only when the visit must proceed early.', ['date' => $appointment->scheduled_at->format('M j, Y')]) }}
                    </p>
                    <label class="inline-flex items-center gap-2 mb-4 text-body-sm text-on-surface">
                        <input id="appointment-emergency-override" class="rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" />
                        {{ __('Emergency — allow day-of statuses before the appointment date') }}
                    </label>
                @endif
                <div class="flex flex-wrap gap-3">
                    @foreach ($statusActions as $statusValue => $label)
                        @php
                            $targetStatus = App\Enums\AppointmentStatus::from($statusValue);
                            $requiresDay = $appointment->statusRequiresAppointmentDay($targetStatus);
                            $canApplyNow = $appointment->canApplyStatus($targetStatus);
                            $lockedUntilEmergency = $requiresDay && $needsDayGate && ! $canApplyNow;
                        @endphp
                        @if ($appointment->status->value !== $statusValue)
                            <form action="{{ route('tenant.appointments.status', $appointment) }}" method="POST" @if ($lockedUntilEmergency) data-emergency-gated @endif>
                                @csrf
                                @method('PATCH')
                                <input name="status" type="hidden" value="{{ $statusValue }}" />
                                <input name="is_emergency" type="hidden" value="0" data-emergency-flag />
                                <button
                                    class="px-4 py-2 border border-slate-200 rounded-lg text-body-md text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white"
                                    type="submit"
                                    @disabled($lockedUntilEmergency)
                                    @if ($lockedUntilEmergency) data-emergency-button @endif
                                >
                                    {{ $label }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
                @error('status')
                    <p class="mt-3 text-body-sm text-error">{{ $message }}</p>
                @enderror
                @if ($appointment->checked_in_at)
                    <p class="text-body-sm text-on-surface-variant mt-4">
                        {{ __('Checked in at :time', ['time' => $appointment->checked_in_at->format('g:i A')]) }}
                    </p>
                @endif
                @if ($appointment->completed_at)
                    <p class="text-body-sm text-on-surface-variant mt-1">
                        {{ __('Completed at :time', ['time' => $appointment->completed_at->format('g:i A')]) }}
                    </p>
                @endif
            </div>
            @if ($needsDayGate)
                <script>
                    (() => {
                        const toggle = document.getElementById('appointment-emergency-override');
                        if (!toggle) return;

                        const sync = () => {
                            document.querySelectorAll('[data-emergency-gated]').forEach((form) => {
                                const flag = form.querySelector('[data-emergency-flag]');
                                const button = form.querySelector('[data-emergency-button]');
                                if (flag) flag.value = toggle.checked ? '1' : '0';
                                if (button) button.disabled = !toggle.checked;
                            });
                        };

                        toggle.addEventListener('change', sync);
                        sync();
                    })();
                </script>
            @endif
        @endcan
    </div>
</x-tenant-layout>
