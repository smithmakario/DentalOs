@php
    use App\Support\AppointmentWizard;
@endphp

<x-tenant-layout>
    <main @class(['max-w-7xl mx-auto px-6 py-8', 'max-w-4xl' => $wizard['step'] === 4])>
        <div class="mb-6">
            <a class="inline-flex items-center gap-1 text-body-md text-primary hover:underline" href="{{ route('tenant.appointments.index') }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to appointments') }}
            </a>
        </div>

        @include('tenant.appointments.wizard._stepper')

        @switch($wizard['step'])
            @case(1)
                @include('tenant.appointments.wizard.services')
                @break
            @case(2)
                @include('tenant.appointments.wizard.provider')
                @break
            @case(3)
                @include('tenant.appointments.wizard.time')
                @break
            @case(4)
                @include('tenant.appointments.wizard.review')
                @break
        @endswitch
    </main>
</x-tenant-layout>
