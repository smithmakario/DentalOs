<x-tenant-layout>
    <div class="p-xl max-w-7xl mx-auto">
        <div class="mb-xl flex justify-between items-end">
            <div>
                <h2 class="font-h1 text-on-surface">{{ __('Branch Overview') }}</h2>
                <p class="text-body-lg text-outline mt-1">
                    {{ __('Welcome back, :name. Daily operations for :branch.', ['name' => $staff->full_name, 'branch' => $branchName]) }}
                </p>
            </div>
            <div class="flex gap-md">
                @if ($staffCanManageAppointments ?? false)
                    <a href="{{ route('tenant.appointments.create') }}" class="flex items-center gap-2 px-md py-sm bg-white border border-outline-variant text-on-surface rounded-xl font-label-md hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-sm">event</span>
                        {{ __('Schedule') }}
                    </a>
                @endif
                @if ($staffCanManagePatients ?? false)
                    <a href="{{ route('tenant.patients.create') }}" class="flex items-center gap-2 px-md py-sm bg-primary text-white rounded-xl font-label-md hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-sm">person_add</span>
                        {{ __('Add Patient') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mb-xl">
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-primary-fixed rounded-lg">
                        <span class="material-symbols-outlined text-primary">groups</span>
                    </div>
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Total Patients') }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($patientCount) }}</h3>
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-secondary-container rounded-lg">
                        <span class="material-symbols-outlined text-secondary">calendar_month</span>
                    </div>
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __("Today's Appointments") }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($todayAppointments) }}</h3>
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-surface-container-highest rounded-lg">
                        <span class="material-symbols-outlined text-on-surface-variant">schedule</span>
                    </div>
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Upcoming') }}</p>
                    <h3 class="font-h2 text-on-surface">{{ number_format($upcomingAppointments->count()) }}</h3>
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-tertiary-fixed rounded-lg">
                        <span class="material-symbols-outlined text-tertiary">medical_services</span>
                    </div>
                </div>
                <div class="mt-md">
                    <p class="text-body-sm text-outline">{{ __('Your Role') }}</p>
                    <h3 class="font-h3 text-on-surface">{{ ucfirst(str_replace('_', ' ', $staff->role->value)) }}</h3>
                </div>
            </div>
        </div>

        @if (($staffCanManageAppointments ?? false) && $todaySchedule->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-xl">
                <div class="p-xl border-b border-slate-100">
                    <h3 class="font-h3 text-on-surface">{{ __("Today's Schedule") }}</h3>
                    <p class="text-body-sm text-outline">{{ __('All visits scheduled for today at this branch.') }}</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($todaySchedule as $appointment)
                        <a class="flex items-center justify-between px-xl py-md hover:bg-slate-50 transition-colors" href="{{ route('tenant.appointments.show', $appointment) }}">
                            <div>
                                <p class="font-body-md font-semibold text-on-surface">{{ $appointment->patient->full_name }}</p>
                                <p class="text-body-sm text-outline">{{ $appointment->provider->full_name }} · {{ $appointment->title ?: __('Visit') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-label-md text-on-surface">{{ $appointment->scheduled_at->format('g:i A') }}</p>
                                <p class="text-body-sm text-outline">{{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-xl border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h3 class="font-h3 text-on-surface">{{ __('Upcoming Appointments') }}</h3>
                    <p class="text-body-sm text-outline">{{ __('Next scheduled visits at this branch.') }}</p>
                </div>
                @if ($staffCanManageAppointments ?? false)
                    <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.appointments.index') }}">{{ __('View All') }}</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface text-outline font-label-sm uppercase tracking-wider">
                            <th class="px-xl py-md">{{ __('When') }}</th>
                            <th class="px-xl py-md">{{ __('Patient') }}</th>
                            <th class="px-xl py-md">{{ __('Provider') }}</th>
                            <th class="px-xl py-md">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($upcomingAppointments as $appointment)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-xl py-md text-body-md text-on-surface">{{ $appointment->scheduled_at->format('M j, g:i A') }}</td>
                                <td class="px-xl py-md">
                                    <a class="font-body-md font-semibold text-on-surface hover:text-primary" href="{{ route('tenant.appointments.show', $appointment) }}">
                                        {{ $appointment->patient->full_name }}
                                    </a>
                                </td>
                                <td class="px-xl py-md text-body-md text-outline">{{ $appointment->provider->full_name }}</td>
                                <td class="px-xl py-md">
                                    <span class="px-3 py-1 bg-surface-container rounded-full font-label-sm text-on-surface-variant">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-xl py-md text-body-md text-outline text-center" colspan="4">
                                    {{ __('No upcoming appointments.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-tenant-layout>
