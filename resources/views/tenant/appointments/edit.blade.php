<x-tenant-layout>
    <div class="p-8 max-w-4xl mx-auto">
        <div class="mb-8">
            <a class="inline-flex items-center gap-1 text-body-md text-primary hover:underline mb-4" href="{{ route('tenant.appointments.show', $appointment) }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to appointment') }}
            </a>
            <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Edit Appointment') }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">
                {{ $appointment->patient->full_name }} · {{ $appointment->scheduled_at->format('M j, Y g:i A') }}
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8">
            <form action="{{ route('tenant.appointments.update', $appointment) }}" method="POST">
                @csrf
                @method('PUT')
                @include('tenant.appointments._form', ['appointment' => $appointment, 'patients' => $patients, 'providers' => $providers])

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                    <a class="px-6 py-2.5 border border-slate-200 rounded-lg text-body-md text-slate-600 hover:bg-slate-50" href="{{ route('tenant.appointments.show', $appointment) }}">
                        {{ __('Cancel') }}
                    </a>
                    <button class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity" type="submit">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6">
            <form action="{{ route('tenant.appointments.destroy', $appointment) }}" method="POST" onsubmit="return confirm(@js(__('Archive this appointment?')))">
                @csrf
                @method('DELETE')
                <button class="px-4 py-2 text-error border border-error/30 rounded-lg text-body-md hover:bg-error-container/30" type="submit">
                    {{ __('Archive Appointment') }}
                </button>
            </form>
        </div>
    </div>
</x-tenant-layout>
