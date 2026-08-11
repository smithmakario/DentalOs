@php
    use App\Support\AppointmentWizard;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <aside class="lg:col-span-4 space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="font-h3 text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">location_on</span>
                {{ __('Branch') }}
            </h2>
            <div class="p-4 rounded-lg border-2 border-primary bg-primary-fixed-dim/10">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-h3 text-primary text-base">{{ tenant('name') }}</div>
                        <div class="font-body-sm text-on-surface-variant mt-1">
                            {{ $branchProfile?->address ?: __('Primary clinic location') }}
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 border-primary flex items-center justify-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                    </div>
                </div>
            </div>
        </div>

        @if ($selectedService)
            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Selected Service') }}</h3>
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">medical_services</span>
                    <div>
                        <p class="font-label-md text-on-surface">{{ $selectedService->name }}</p>
                        <p class="font-body-sm text-on-surface-variant">{{ $selectedService->duration_minutes }} {{ __('min') }} · {{ \App\Support\Money::naira($selectedService->price) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-secondary-container/10 border border-secondary/20 p-5 rounded-xl">
            <div class="flex gap-3">
                <span class="material-symbols-outlined text-secondary">info</span>
                <div>
                    <p class="font-label-md text-on-secondary-container">{{ __('Clinical Standards') }}</p>
                    <p class="font-body-sm text-on-secondary-container/80 mt-1">{{ __('All providers are licensed dentists following our branch hygiene and care protocols.') }}</p>
                </div>
            </div>
        </div>
    </aside>

    <section class="lg:col-span-8 space-y-6">
        <div class="flex justify-between items-end mb-2">
            <div>
                <h1 class="font-h2 text-on-surface">{{ __('Available Dentists') }}</h1>
                <p class="font-body-md text-on-surface-variant">{{ __('Choose your preferred provider at :branch.', ['branch' => tenant('name')]) }}</p>
            </div>
        </div>

        @if ($dentists->isEmpty())
            <div class="bg-white border border-outline-variant rounded-xl p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-outline mb-4">person_off</span>
                <h3 class="font-h3 text-h3 text-on-surface mb-2">{{ __('No dentists available') }}</h3>
                <p class="font-body-md text-on-surface-variant mb-6">{{ __('Add dentist staff members before scheduling appointments.') }}</p>
                @can('create', App\Models\Staff::class)
                    <a class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" href="{{ route('tenant.staff.create') }}">
                        {{ __('Add Staff Member') }}
                    </a>
                @endcan
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($dentists as $dentist)
                    @php
                        $nextSlot = $dentistAvailability[$dentist->id] ?? null;
                        $isSelected = (int) $wizard['provider_id'] === $dentist->id;
                    @endphp
                    <div @class([
                        'bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group border-l-4',
                        'border-primary border-l-primary shadow-md' => $isSelected,
                        'border-slate-200 border-l-transparent hover:border-l-primary' => ! $isSelected,
                    ])>
                        <div class="p-5">
                            <div class="flex gap-4 items-start">
                                <img alt="{{ $dentist->full_name }}" class="w-20 h-20 rounded-lg object-cover shadow-sm" src="{{ $dentist->avatarUrl() }}" />
                                <div class="flex-1">
                                    <h3 class="font-h3 text-on-surface">{{ $dentist->full_name }}</h3>
                                    <p class="font-body-sm text-on-surface-variant">{{ $dentist->specialization ?: __('General Dentistry') }}</p>
                                    @if ($dentist->years_of_experience !== null)
                                        <p class="font-body-sm text-on-surface-variant mt-1">{{ $dentist->years_of_experience }} {{ __('years experience') }}</p>
                                    @endif
                                    @if ($nextSlot)
                                        <div class="mt-3 flex items-center gap-2 text-primary font-label-md bg-primary/5 p-1.5 rounded w-fit">
                                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                            {{ __('Next: :datetime', ['datetime' => $nextSlot->format('M j, g:i A')]) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
                                <a class="px-4 py-2 bg-primary text-on-primary rounded font-label-md hover:opacity-90 transition-colors" href="{{ AppointmentWizard::url($wizard, 3, ['provider_id' => $dentist->id]) }}">
                                    {{ __('Select Provider') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

<div class="mt-12 pt-8 border-t border-slate-200 flex justify-between items-center">
    <a class="flex items-center gap-2 px-6 py-3 border border-outline-variant text-on-surface font-label-md rounded-xl hover:bg-surface-container-low transition-all" href="{{ AppointmentWizard::url($wizard, 1) }}">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        {{ __('Back') }}
    </a>
    <p class="hidden sm:block font-body-sm text-on-surface-variant italic">{{ __('Select a provider to continue.') }}</p>
</div>
