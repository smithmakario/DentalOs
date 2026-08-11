@php
    $avatarClasses = ['bg-blue-100 text-blue-600', 'bg-teal-100 text-teal-600', 'bg-orange-100 text-orange-600', 'bg-indigo-100 text-indigo-600'];
    $initialsFor = fn (string $first, string $last): string => strtoupper(substr($first, 0, 1).substr($last, 0, 1));
@endphp

<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Patient Management') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Register, search, and manage patient records for :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            @can('create', App\Models\Patient::class)
                <a href="{{ route('tenant.patients.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    {{ __('Add Patient') }}
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg material-symbols-outlined">groups</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($totalPatients) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Total Patients') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-secondary-container/20 text-secondary rounded-lg material-symbols-outlined">verified_user</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($activePatients) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Active Patients') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-tertiary-fixed text-tertiary rounded-lg material-symbols-outlined">trending_up</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($newThisMonth) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('New This Month') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <form action="{{ route('tenant.patients.index') }}" class="flex gap-4 items-center" method="GET">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input
                            class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-72 focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
                            name="search"
                            placeholder="{{ __('Search by name, email, or phone...') }}"
                            type="search"
                            value="{{ $search }}"
                        />
                    </div>
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50" type="submit">
                        {{ __('Search') }}
                    </button>
                    @if ($search !== '')
                        <a class="text-sm text-primary hover:underline" href="{{ route('tenant.patients.index') }}">{{ __('Clear') }}</a>
                    @endif
                </form>
                @if ($patients->total() > 0)
                    <div class="text-body-sm font-body-sm text-on-surface-variant">
                        {{ __('Showing') }}
                        <span class="font-bold">{{ $patients->firstItem() }}-{{ $patients->lastItem() }}</span>
                        {{ __('of') }}
                        <span class="font-bold">{{ number_format($patients->total()) }}</span>
                        {{ __('patients') }}
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Contact') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Insurance') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($patients as $patient)
                            @php $avatarClass = $avatarClasses[$loop->index % count($avatarClasses)]; @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $avatarClass }} flex items-center justify-center font-bold">
                                            {{ $initialsFor($patient->first_name, $patient->last_name) }}
                                        </div>
                                        <div>
                                            <a class="font-medium text-slate-900 hover:text-primary" href="{{ route('tenant.patients.show', $patient) }}">
                                                {{ $patient->full_name }}
                                            </a>
                                            <div class="text-xs text-slate-500">
                                                {{ $patient->date_of_birth ? $patient->date_of_birth->format('M j, Y') : __('DOB not set') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-900">{{ $patient->phone ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $patient->email ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $patient->insurance_provider ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($patient->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            {{ __('ACTIVE') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ __('INACTIVE') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" href="{{ route('tenant.patients.show', $patient) }}" title="{{ __('View') }}">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
                                        @can('update', $patient)
                                            <a class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" href="{{ route('tenant.patients.edit', $patient) }}" title="{{ __('Edit') }}">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-body-md text-on-surface-variant" colspan="5">
                                    {{ $search !== '' ? __('No patients match your search.') : __('No patients registered yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($patients->hasPages())
                <div class="p-6 border-t border-slate-100">
                    {{ $patients->links() }}
                </div>
            @endif
        </div>
    </div>
</x-tenant-layout>
