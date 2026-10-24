<x-clinic-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="font-h1 text-on-surface mb-2">{{ __('Appointments') }}</h2>
            <p class="text-on-surface-variant">{{ __('Clinic-wide appointments with branch filters.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalAppointments) }}</div>
                <div class="text-sm text-slate-500">{{ __('Listed appointments') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($branchesWithAppointments) }}</div>
                <div class="text-sm text-slate-500">{{ __('Branches with appointments') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <x-clinic.branch-filter :action="route('clinic.appointments.index')" :branches="$branches" :selected-branch="$selectedBranch" />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('When') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Patient') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Branch') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ optional($appointment['scheduled_at'])->format('M j, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $appointment['patient_name'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $appointment['branch_name'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ is_object($appointment['status']) ? $appointment['status']->value : $appointment['status'] }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $appointment['branch_url'] }}" class="text-sm text-primary hover:underline">{{ __('Open at branch') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No appointments found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($appointments->hasPages())
                <div class="p-4 border-t">{{ $appointments->links() }}</div>
            @endif
        </div>
    </div>
</x-clinic-layout>
