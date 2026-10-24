<x-clinic-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="font-h1 text-on-surface mb-2">{{ __('Staff') }}</h2>
            <p class="text-on-surface-variant">{{ __('Staff accounts across every branch.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalStaff) }}</div>
                <div class="text-sm text-slate-500">{{ __('Staff members') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($branchesWithStaff) }}</div>
                <div class="text-sm text-slate-500">{{ __('Branches with staff') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <x-clinic.branch-filter :action="route('clinic.staff.index')" :branches="$branches" :selected-branch="$selectedBranch" />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Branch') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Role') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($staffMembers as $member)
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium">{{ $member['full_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $member['email'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $member['branch_name'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ is_object($member['role']) ? $member['role']->label() : $member['role'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ $member['is_active'] ? __('Active') : __('Inactive') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $member['branch_url'] }}" class="text-sm text-primary hover:underline">{{ __('Open at branch') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No staff found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-clinic-layout>
