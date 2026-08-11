@php
    use App\Support\AppointmentWizard;

    $prevMonth = $calendarMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $calendarMonth->copy()->addMonth()->format('Y-m');
    $selectedTime = $wizard['time'];
@endphp

<style>
    .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
</style>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <aside class="lg:col-span-3 space-y-6">
        <div class="bg-surface-container-lowest border border-slate-200 rounded-xl p-6 shadow-sm">
            <h3 class="font-h3 text-on-surface mb-4">{{ __('Your Selection') }}</h3>
            <div class="flex flex-col items-center text-center pb-6 border-b border-slate-100">
                <div class="w-20 h-20 rounded-full overflow-hidden mb-3 ring-4 ring-primary-fixed">
                    <img alt="{{ $selectedProvider->full_name }}" class="w-full h-full object-cover" src="{{ $selectedProvider->avatarUrl() }}" />
                </div>
                <h4 class="font-h3 text-slate-900">{{ $selectedProvider->full_name }}</h4>
                <p class="text-body-sm text-slate-500">
                    {{ $selectedProvider->specialization ?: __('General Dentistry') }}
                    @if ($selectedProvider->years_of_experience !== null)
                        · {{ $selectedProvider->years_of_experience }} {{ __('years exp.') }}
                    @endif
                </p>
            </div>
            <div class="pt-6 space-y-3">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-lg">medical_services</span>
                    <div class="flex flex-col">
                        <span class="text-label-sm text-slate-400 uppercase">{{ __('Service') }}</span>
                        <span class="text-body-md font-medium text-slate-800">{{ $selectedService->name }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-lg">payments</span>
                    <div class="flex flex-col">
                        <span class="text-label-sm text-slate-400 uppercase">{{ __('Estimated Cost') }}</span>
                        <span class="text-body-md font-medium text-slate-800">{{ \App\Support\Money::naira($selectedService->price) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-primary-fixed-dim/20 rounded-xl p-4 border border-primary-fixed-dim/30">
            <p class="text-body-sm text-on-primary-fixed-variant leading-relaxed">
                <span class="material-symbols-outlined text-primary mr-1 text-sm align-middle">info</span>
                {{ __('Appointments cancelled less than 24 hours in advance may incur a fee.') }}
            </p>
        </div>
    </aside>

    <section class="lg:col-span-9 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row h-full min-h-[600px]">
            <div class="flex-1 p-8 border-b md:border-b-0 md:border-r border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-h2 text-on-surface">{{ $calendarMonth->format('F Y') }}</h2>
                    <div class="flex gap-2">
                        <a class="p-2 hover:bg-slate-50 rounded-lg border border-slate-200 transition-colors" href="{{ AppointmentWizard::url($wizard, 3, ['month' => $prevMonth, 'date' => $calendarMonth->copy()->subMonth()->endOfMonth()->format('Y-m-d')]) }}">
                            <span class="material-symbols-outlined text-slate-600">chevron_left</span>
                        </a>
                        <a class="p-2 hover:bg-slate-50 rounded-lg border border-slate-200 transition-colors" href="{{ AppointmentWizard::url($wizard, 3, ['month' => $nextMonth, 'date' => $calendarMonth->copy()->addMonth()->startOfMonth()->format('Y-m-d')]) }}">
                            <span class="material-symbols-outlined text-slate-600">chevron_right</span>
                        </a>
                    </div>
                </div>

                <div class="calendar-grid mb-4">
                    @foreach (['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'] as $dayLabel)
                        <span class="text-center text-label-sm text-slate-400 py-2">{{ __($dayLabel) }}</span>
                    @endforeach
                </div>

                <div class="calendar-grid gap-1">
                    @foreach ($calendarDays as $day)
                        @if (! $day['in_month'])
                            <div class="aspect-square flex items-center justify-center text-slate-300 text-body-sm">{{ $day['date']->format('j') }}</div>
                        @elseif ($day['is_past'] || $day['date']->isSunday())
                            <div class="aspect-square flex flex-col items-center justify-center text-slate-300 text-body-sm">{{ $day['date']->format('j') }}</div>
                        @else
                            <a @class([
                                'aspect-square flex flex-col items-center justify-center rounded-lg transition-colors border',
                                'bg-primary text-on-primary shadow-md ring-2 ring-primary ring-offset-2 border-primary' => $day['is_selected'],
                                'hover:bg-slate-50 border-transparent' => ! $day['is_selected'],
                            ]) href="{{ AppointmentWizard::url($wizard, 3, ['date' => $day['date']->format('Y-m-d'), 'month' => $calendarMonth->format('Y-m')]) }}">
                                <span @class(['text-body-md', 'font-bold' => $day['is_selected'], 'text-slate-700' => ! $day['is_selected']])>{{ $day['date']->format('j') }}</span>
                                @if ($day['has_availability'])
                                    <div @class(['w-1 h-1 rounded-full mt-1', 'bg-white' => $day['is_selected'], 'bg-primary' => ! $day['is_selected']])></div>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="w-full md:w-80 bg-slate-50/50 p-8">
                <div class="mb-6">
                    <h4 class="font-h3 text-on-surface">{{ __('Available Slots') }}</h4>
                    <p class="text-body-sm text-slate-500">{{ $selectedDate->format('l, M j') }}</p>
                </div>

                <div class="space-y-8 max-h-[400px] overflow-y-auto pr-2">
                    @foreach (['morning' => ['icon' => 'wb_sunny', 'label' => __('MORNING')], 'afternoon' => ['icon' => 'light_mode', 'label' => __('AFTERNOON')], 'evening' => ['icon' => 'dark_mode', 'label' => __('EVENING')]] as $period => $meta)
                        @if (count($timeSlots[$period]) > 0)
                            <div>
                                <h5 class="font-label-sm text-slate-400 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">{{ $meta['icon'] }}</span>
                                    {{ $meta['label'] }}
                                </h5>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($timeSlots[$period] as $slot)
                                        @if ($slot['available'])
                                            <a @class([
                                                'py-2 px-3 border rounded-lg text-body-md text-center transition-all',
                                                'border-primary bg-primary-container text-on-primary-container font-semibold shadow-sm' => $selectedTime === $slot['time'],
                                                'border-slate-200 bg-white hover:border-primary hover:text-primary' => $selectedTime !== $slot['time'],
                                            ]) href="{{ AppointmentWizard::url($wizard, 4, ['date' => $selectedDate->format('Y-m-d'), 'time' => $slot['time']]) }}">
                                                {{ $slot['label'] }}
                                            </a>
                                        @else
                                            <span class="py-2 px-3 border border-slate-200 rounded-lg bg-white text-body-md opacity-40 cursor-not-allowed text-center">{{ $slot['label'] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>

<div class="mt-12 flex items-center justify-between border-t border-slate-200 pt-8">
    <a class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 font-medium hover:bg-slate-50 active:scale-95 transition-all flex items-center gap-2" href="{{ AppointmentWizard::url($wizard, 2) }}">
        <span class="material-symbols-outlined text-md">arrow_back</span>
        {{ __('Back to Providers') }}
    </a>
    <div class="flex items-center gap-6">
        @if ($wizard['date'] && $wizard['time'])
            <div class="hidden sm:block text-right">
                <p class="text-label-sm text-slate-500 uppercase">{{ __('Selected Appointment') }}</p>
                <p class="text-body-md font-semibold text-slate-900">
                    {{ $selectedDate->format('D, M j') }} {{ __('at') }} {{ Carbon\Carbon::parse($wizard['time'])->format('g:i A') }}
                </p>
            </div>
            <a class="px-8 py-3 bg-primary text-on-primary rounded-lg font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95 flex items-center gap-2" href="{{ AppointmentWizard::url($wizard, 4) }}">
                {{ __('Review Details') }}
                <span class="material-symbols-outlined text-md">arrow_forward</span>
            </a>
        @else
            <p class="font-body-sm text-on-surface-variant italic">{{ __('Select a date and time to continue.') }}</p>
        @endif
    </div>
</div>
