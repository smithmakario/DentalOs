<x-clinic-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="font-h1 text-on-surface mb-2">{{ __('Treatments') }}</h2>
            <p class="text-on-surface-variant">{{ __('Treatment plans across all branches.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalPlans) }}</div>
                <div class="text-sm text-slate-500">{{ __('Treatment plans') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($branchesWithPlans) }}</div>
                <div class="text-sm text-slate-500">{{ __('Branches with plans') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <x-clinic.branch-filter :action="route('clinic.treatments.index')" :branches="$branches" :selected-branch="$selectedBranch" />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Plan') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Patient') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Branch') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($plans as $plan)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium">{{ $plan['title'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ $plan['patient_name'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $plan['branch_name'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ is_object($plan['status']) ? $plan['status']->value : $plan['status'] }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $plan['branch_url'] }}" class="text-sm text-primary hover:underline">{{ __('Open at branch') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No treatment plans found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($plans->hasPages())
                <div class="p-4 border-t">{{ $plans->links() }}</div>
            @endif
        </div>
    </div>
</x-clinic-layout>
