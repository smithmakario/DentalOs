<x-clinic-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-on-surface mb-2">{{ __('Patients') }}</h2>
                <p class="text-on-surface-variant">{{ __('All patients across :clinic, filterable by branch.', ['clinic' => $organization->name]) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalPatients) }}</div>
                <div class="text-sm text-slate-500">{{ __('Total patients') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($activePatients) }}</div>
                <div class="text-sm text-slate-500">{{ __('Active patients') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($branchesWithPatients) }}</div>
                <div class="text-sm text-slate-500">{{ __('Branches with patients') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <x-clinic.branch-filter
                    :action="route('clinic.patients.index')"
                    :branches="$branches"
                    :selected-branch="$selectedBranch"
                    :extra="['search' => $search]"
                >
                    <div>
                        <label for="search" class="block text-xs font-semibold text-on-surface-variant mb-1">{{ __('Search') }}</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm w-72" placeholder="{{ __('Name, email, phone...') }}" />
                    </div>
                </x-clinic.branch-filter>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Patient') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Branch') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Contact') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($patients as $patient)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $patient['full_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $patient['patient_id_string'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $patient['branch_name'] }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div>{{ $patient['email'] ?: '—' }}</div>
                                    <div class="text-slate-500">{{ $patient['phone'] ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $patient['is_active'] ? __('Active') : __('Inactive') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $patient['branch_url'] }}" class="text-sm text-primary hover:underline">{{ __('Open at branch') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No patients found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($patients->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $patients->links() }}</div>
            @endif
        </div>
    </div>
</x-clinic-layout>
