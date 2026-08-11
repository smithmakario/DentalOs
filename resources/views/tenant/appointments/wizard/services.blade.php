@php
    use App\Support\AppointmentWizard;
@endphp

<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
    <div>
        <h2 class="font-h2 text-h2 text-on-surface mb-2">{{ __('Select a Service') }}</h2>
        <p class="font-body-lg text-body-lg text-on-surface-variant">{{ __('Choose the dental procedure for this visit.') }}</p>
    </div>
    <form action="{{ route('tenant.appointments.create') }}" class="relative w-full md:w-80" method="GET">
        <input name="step" type="hidden" value="1" />
        @if ($wizard['patient_id'])
            <input name="patient_id" type="hidden" value="{{ $wizard['patient_id'] }}" />
        @endif
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
        <input class="w-full pl-10 pr-4 py-2 bg-white border border-outline-variant rounded-xl focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md" name="search" placeholder="{{ __('Search services...') }}" type="text" value="{{ $wizard['search'] }}" />
    </form>
</div>

@if ($services->isEmpty())
    <div class="bg-white border border-outline-variant rounded-xl p-12 text-center">
        <span class="material-symbols-outlined text-5xl text-outline mb-4">medical_services</span>
        <h3 class="font-h3 text-h3 text-on-surface mb-2">{{ __('No services available') }}</h3>
        <p class="font-body-md text-on-surface-variant mb-6">{{ __('Add clinic services before scheduling appointments.') }}</p>
        @can('create', App\Models\ClinicService::class)
            <a class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" href="{{ route('tenant.clinic-services.create') }}">
                <span class="material-symbols-outlined text-[18px]">add</span>
                {{ __('Add Service') }}
            </a>
        @endcan
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($services as $service)
            @php
                $icon = $service->icon ?: 'medical_services';
                $isSelected = (int) $wizard['service_id'] === $service->id;
            @endphp
            <a @class([
                'bg-white p-6 rounded-xl relative group flex flex-col h-full transition-all cursor-pointer no-underline',
                'border-2 border-primary shadow-md' => $isSelected || $service->is_recommended,
                'border border-outline-variant hover:border-primary hover:shadow-lg' => ! $isSelected && ! $service->is_recommended,
            ]) href="{{ AppointmentWizard::url($wizard, 2, ['service_id' => $service->id]) }}">
                @if ($service->is_recommended)
                    <div class="absolute -top-3 left-6 px-3 py-1 bg-primary text-on-primary rounded-full text-[10px] font-bold uppercase tracking-wider">{{ __('Recommended') }}</div>
                @endif
                <div @class([
                    'w-12 h-12 rounded-lg flex items-center justify-center mb-4 transition-colors',
                    'bg-primary text-on-primary' => $isSelected || $service->is_recommended,
                    'bg-surface-container-low text-primary group-hover:bg-primary group-hover:text-on-primary' => ! $isSelected && ! $service->is_recommended,
                ])>
                    <span class="material-symbols-outlined text-3xl">{{ $icon }}</span>
                </div>
                <h3 class="font-h3 text-h3 text-on-surface mb-2">{{ $service->name }}</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant mb-6 flex-grow">{{ $service->description }}</p>
                <div class="flex items-center justify-between mt-auto pt-4 border-t border-surface-container-high">
                    <div class="flex items-center gap-1 text-on-surface-variant">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        <span class="font-label-md">{{ $service->duration_minutes }} {{ __('min') }}</span>
                    </div>
                    <span class="font-label-md text-primary">{{ \App\Support\Money::naira($service->price) }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endif
