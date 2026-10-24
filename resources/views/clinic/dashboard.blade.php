<x-clinic-layout>
    <div class="p-xl max-w-7xl mx-auto">
        <div class="mb-xl">
            <h2 class="font-h1 text-on-surface">{{ __('Clinic Overview') }}</h2>
            <p class="text-body-lg text-outline mt-1">
                {{ __('Welcome back, :name. Cross-branch snapshot for :clinic.', ['name' => $staff->full_name, 'clinic' => $organization->name]) }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mb-xl">
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Branches') }}</p>
                <h3 class="font-h2 text-on-surface mt-2">{{ number_format($stats['branch_count']) }}</h3>
            </div>
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Patients') }}</p>
                <h3 class="font-h2 text-on-surface mt-2">{{ number_format($stats['patient_count']) }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __(':count branches with patients', ['count' => $stats['branches_with_patients']]) }}</p>
            </div>
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __("Today's Appointments") }}</p>
                <h3 class="font-h2 text-on-surface mt-2">{{ number_format($stats['today_appointments']) }}</h3>
            </div>
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Staff / Services') }}</p>
                <h3 class="font-h2 text-on-surface mt-2">{{ number_format($stats['staff_count']) }} / {{ number_format($stats['service_count']) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg mb-xl">
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Upcoming appointments') }}</p>
                <h3 class="font-h3 text-on-surface mt-2">{{ number_format($stats['upcoming_appointments']) }}</h3>
            </div>
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Treatment plans') }}</p>
                <h3 class="font-h3 text-on-surface mt-2">{{ number_format($stats['treatment_count']) }}</h3>
            </div>
            <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                <p class="text-body-sm text-outline">{{ __('Invoices') }}</p>
                <h3 class="font-h3 text-on-surface mt-2">{{ number_format($stats['invoice_count']) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-xl border-b border-slate-100">
                <h3 class="font-h3 text-on-surface">{{ __("Today's Schedule") }}</h3>
                <p class="text-body-sm text-outline">{{ __('Visits across every branch today.') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider text-slate-500">{{ __('Time') }}</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider text-slate-500">{{ __('Patient') }}</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider text-slate-500">{{ __('Branch') }}</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider text-slate-500">{{ __('Provider') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($stats['today_schedule'] as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm">{{ optional($row['scheduled_at'])->format('H:i') }}</td>
                                <td class="px-6 py-3 text-sm">{{ $row['patient_name'] ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm">{{ $row['branch_name'] }}</td>
                                <td class="px-6 py-3 text-sm">{{ $row['provider_name'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">{{ __('No appointments scheduled today.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-clinic-layout>
