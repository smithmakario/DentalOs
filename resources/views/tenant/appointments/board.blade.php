<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Day Board') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Front-desk view of appointments by provider for :date.', ['date' => $boardDate->format('D, M j, Y')]) }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('tenant.appointments.index', ['view' => 'list']) }}" class="flex items-center gap-2 border border-slate-200 text-on-surface px-5 py-2.5 rounded-lg font-label-md hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[18px]">view_list</span>
                    {{ __('List view') }}
                </a>
                @can('create', App\Models\Appointment::class)
                    <a href="{{ route('tenant.appointments.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-[18px]">event</span>
                        {{ __('Schedule') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($todayCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __("Today's Appointments") }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($upcomingCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Upcoming') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($completedToday) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Completed Today') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($cancelledCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Cancelled') }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('tenant.appointments.index') }}" class="bg-white border border-slate-200 rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
            <input type="hidden" name="view" value="board" />
            <div>
                <label class="block text-label-sm text-on-surface-variant mb-1" for="board_date">{{ __('Date') }}</label>
                <input id="board_date" name="date" type="date" value="{{ $boardDate->format('Y-m-d') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" />
            </div>
            <div>
                <label class="block text-label-sm text-on-surface-variant mb-1" for="board_provider">{{ __('Provider') }}</label>
                <select id="board_provider" name="provider_id" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                    <option value="">{{ __('All providers') }}</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->id }}" @selected((int) $providerId === $provider->id)>{{ $provider->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-label-sm text-on-surface-variant mb-1" for="board_status">{{ __('Status') }}</label>
                <select id="board_status" name="status" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                    <option value="">{{ __('Any status') }}</option>
                    @foreach (App\Enums\AppointmentStatus::cases() as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90">{{ __('Apply') }}</button>
            <a href="{{ route('tenant.appointments.index', ['view' => 'board', 'date' => today()->format('Y-m-d')]) }}" class="px-5 py-2 border border-slate-200 rounded-lg font-label-md hover:bg-slate-50">{{ __('Today') }}</a>
        </form>

        @if ($boardAppointments->isEmpty())
            <div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-on-surface-variant">
                {{ __('No appointments scheduled for this day.') }}
            </div>
        @else
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                @foreach ($providers as $provider)
                    @php $column = $boardAppointments->get($provider->id, collect()); @endphp
                    @if ($providerId > 0 && (int) $providerId !== $provider->id)
                        @continue
                    @endif
                    @if ($column->isEmpty() && $providerId === 0)
                        @continue
                    @endif
                    <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <header class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                            <div>
                                <h3 class="font-h3 text-on-surface">{{ $provider->full_name }}</h3>
                                <p class="text-body-sm text-on-surface-variant">{{ $column->count() }} {{ __('appointments') }}</p>
                            </div>
                        </header>
                        <div class="divide-y divide-slate-100">
                            @forelse ($column as $appointment)
                                <a href="{{ route('tenant.appointments.show', $appointment) }}" class="block px-5 py-4 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $appointment->scheduled_at->format('g:i A') }} · {{ $appointment->patient->full_name }}</p>
                                            <p class="text-body-sm text-on-surface-variant mt-0.5">
                                                {{ $appointment->title ?: __('Visit') }}
                                                · {{ $appointment->duration_minutes }} {{ __('min') }}
                                            </p>
                                            @if ($appointment->patient->phone)
                                                <p class="text-body-sm text-outline mt-1">{{ $appointment->patient->phone }}</p>
                                            @endif
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border {{ $appointment->status->badgeClasses() }}">
                                            {{ $appointment->status->label() }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="px-5 py-8 text-center text-on-surface-variant text-body-sm">{{ __('No appointments for this provider.') }}</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-tenant-layout>
