@php
    use App\Support\StaffRolePermissions;
@endphp

<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('All Clinics') }}
                </a>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Staff') }} — {{ $organization->name }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ __('Assign branch-specific or global access.') }}</p>
            </div>
            @can('create', [App\Models\StaffMember::class, $organization])
                <a href="{{ route('clinics.staff.create', $organization) }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    {{ __('Add Staff') }}
                </a>
            @endcan
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg">{{ session('status') }}</div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 text-on-surface-variant font-label-sm uppercase">
                        <th class="px-6 py-4">{{ __('Name') }}</th>
                        <th class="px-6 py-4">{{ __('Role') }}</th>
                        <th class="px-6 py-4">{{ __('Branch Access') }}</th>
                        <th class="px-6 py-4">{{ __('Permissions') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($staffMembers as $member)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-on-surface">{{ $member->full_name }}</p>
                                <p class="text-xs text-outline">{{ $member->email }}</p>
                            </td>
                            <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $member->role->value) }}</td>
                            <td class="px-6 py-4">
                                @if ($member->has_global_branch_access)
                                    <span class="px-2 py-1 bg-primary-fixed text-primary rounded-full text-xs font-bold">{{ __('All Branches') }}</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($member->branchAssignments as $assignment)
                                            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded-full text-xs">{{ $assignment->branch?->name }}</span>
                                        @empty
                                            <span class="text-error text-xs">{{ __('No branches') }}</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-body-sm text-outline">
                                {{ implode(', ', StaffRolePermissions::permissionLabels($member->role)) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @can('update', $member)
                                    <a href="{{ route('clinics.staff.edit', [$organization, $member]) }}" class="text-primary font-label-md hover:underline">{{ __('Edit') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-outline">{{ __('No staff members yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
