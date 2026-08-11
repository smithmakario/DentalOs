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
            <div class="flex items-center gap-3">
                @include('tenant.appointments._status-badge', ['status' => $appointment->status])
                @can('update', $appointment)
                    <a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity" href="{{ route('tenant.appointments.edit', $appointment) }}">
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
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Notes') }}</h3>
                <p class="text-body-md text-on-surface whitespace-pre-line">{{ $appointment->notes }}</p>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="font-h3 text-on-surface mb-4">{{ __('Update Status') }}</h3>
            <div class="flex flex-wrap gap-3">
                @foreach ($statusActions as $statusValue => $label)
                    @if ($appointment->status->value !== $statusValue)
                        <form action="{{ route('tenant.appointments.status', $appointment) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input name="status" type="hidden" value="{{ $statusValue }}" />
                            <button
                                class="px-4 py-2 border border-slate-200 rounded-lg text-body-md text-slate-700 hover:bg-slate-50 transition-colors"
                                type="submit"
                            >
                                {{ $label }}
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
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
    </div>
</x-tenant-layout>
