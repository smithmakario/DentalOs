<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Staff Management') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Manage dentists, nurses, receptionists, and other team members at :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            @can('create', App\Models\Staff::class)
                <a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20" href="{{ route('tenant.staff.create') }}">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    {{ __('Add Staff Member') }}
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg material-symbols-outlined">badge</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($totalStaff) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Total Staff') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-green-50 text-green-600 rounded-lg material-symbols-outlined">verified_user</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($activeStaff) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Active Staff') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-secondary-container/20 text-secondary rounded-lg material-symbols-outlined">medical_services</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($providerCount) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Clinical Providers') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <form action="{{ route('tenant.staff.index') }}" class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50" method="GET">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-72 focus:ring-2 focus:ring-primary focus:border-transparent outline-none" name="search" placeholder="{{ __('Search by name, email, or specialty...') }}" type="search" value="{{ $search }}" />
                    </div>
                    <select class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" name="role" onchange="this.form.submit()">
                        <option value="">{{ __('All Roles') }}</option>
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption->value }}" @selected($role === $roleOption->value)>{{ $roleOption->label() }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50" type="submit">{{ __('Filter') }}</button>
                    @if ($search !== '' || $role !== '')
                        <a class="text-sm text-primary hover:underline" href="{{ route('tenant.staff.index') }}">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Staff Member') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Role') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Specialty') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Experience') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($staffMembers as $member)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img alt="{{ $member->full_name }}" class="w-10 h-10 rounded-lg object-cover border border-outline-variant" src="{{ $member->avatarUrl() }}" />
                                        <div>
                                            <div class="font-medium text-on-surface">{{ $member->full_name }}</div>
                                            <div class="text-xs text-slate-500">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ $member->role->label() }}</span>
                                </td>
                                <td class="px-6 py-4 text-body-md text-on-surface-variant">{{ $member->specialization ?: '—' }}</td>
                                <td class="px-6 py-4 text-body-md text-on-surface-variant">
                                    {{ $member->years_of_experience !== null ? $member->years_of_experience.' '.__('years') : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($member->is_active)
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('ACTIVE') }}</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ __('INACTIVE') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="text-primary font-label-md hover:underline mr-4" href="{{ route('tenant.staff.show', $member) }}">{{ __('View') }}</a>
                                    @can('update', $member)
                                        <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.staff.edit', $member) }}">{{ __('Edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-on-surface-variant" colspan="6">{{ __('No staff members found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($staffMembers->hasPages())
                <div class="p-6 border-t border-slate-100">{{ $staffMembers->links() }}</div>
            @endif
        </div>
    </div>
</x-tenant-layout>
