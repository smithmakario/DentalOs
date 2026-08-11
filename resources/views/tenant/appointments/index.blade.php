<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Appointment Scheduling') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Manage visits, provider schedules, and appointment status for :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            @can('create', App\Models\Appointment::class)
                <a href="{{ route('tenant.appointments.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">event</span>
                    {{ __('Schedule Appointment') }}
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg material-symbols-outlined">today</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($todayCount) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __("Today's Appointments") }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-secondary-container/20 text-secondary rounded-lg material-symbols-outlined">schedule</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($upcomingCount) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Upcoming') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-green-50 text-green-600 rounded-lg material-symbols-outlined">task_alt</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($completedToday) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Completed Today') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-error-container text-error rounded-lg material-symbols-outlined">event_busy</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($cancelledCount) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Cancelled') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <form action="{{ route('tenant.appointments.index') }}" class="flex flex-wrap gap-3 items-center" method="GET">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input
                            class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-56 focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
                            name="search"
                            placeholder="{{ __('Search patient...') }}"
                            type="search"
                            value="{{ $search }}"
                        />
                    </div>
                    <select class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary outline-none" name="period">
                        <option value="upcoming" @selected($period === 'upcoming')>{{ __('Upcoming') }}</option>
                        <option value="today" @selected($period === 'today')>{{ __('Today') }}</option>
                        <option value="past" @selected($period === 'past')>{{ __('Past') }}</option>
                    </select>
                    <select class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary outline-none" name="status">
                        <option value="">{{ __('Any Status') }}</option>
                        @foreach (App\Enums\AppointmentStatus::cases() as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>
                                {{ ucfirst(str_replace('_', ' ', $statusOption->value)) }}
                            </option>
                        @endforeach
                    </select>
                    <select class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary outline-none" name="provider_id">
                        <option value="">{{ __('Any Provider') }}</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}" @selected($providerId === $provider->id)>{{ $provider->full_name }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50" type="submit">{{ __('Filter') }}</button>
                    @if ($search !== '' || $status !== '' || $providerId > 0 || $period !== 'upcoming')
                        <a class="text-sm text-primary hover:underline" href="{{ route('tenant.appointments.index') }}">{{ __('Clear') }}</a>
                    @endif
                </form>
                @if ($appointments->total() > 0)
                    <div class="text-body-sm font-body-sm text-on-surface-variant">
                        {{ __('Showing') }}
                        <span class="font-bold">{{ $appointments->firstItem() }}-{{ $appointments->lastItem() }}</span>
                        {{ __('of') }}
                        <span class="font-bold">{{ number_format($appointments->total()) }}</span>
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('When') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Provider') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Title') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($appointments as $appointment)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $appointment->scheduled_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $appointment->scheduled_at->format('g:i A') }} · {{ $appointment->duration_minutes }} {{ __('min') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <a class="font-medium text-slate-900 hover:text-primary" href="{{ route('tenant.patients.show', $appointment->patient) }}">
                                        {{ $appointment->patient->full_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $appointment->provider->full_name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $appointment->title ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @include('tenant.appointments._status-badge', ['status' => $appointment->status])
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" href="{{ route('tenant.appointments.show', $appointment) }}" title="{{ __('View') }}">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
                                        <a class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" href="{{ route('tenant.appointments.edit', $appointment) }}" title="{{ __('Edit') }}">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-body-md text-on-surface-variant" colspan="6">
                                    {{ __('No appointments found for the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($appointments->hasPages())
                <div class="p-6 border-t border-slate-100">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-tenant-layout>
