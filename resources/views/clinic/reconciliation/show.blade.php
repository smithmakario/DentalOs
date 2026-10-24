<x-clinic-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="font-h1 text-on-surface mb-2">{{ __('Reconciliation') }}</h2>
            <p class="text-on-surface-variant">{{ __('Daily collections rolled up across branches.') }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-8">
            <x-clinic.branch-filter
                :action="route('clinic.reconciliation')"
                :branches="$branches"
                :selected-branch="$selectedBranch"
                :extra="['date' => $date->toDateString()]"
            >
                <div>
                    <label for="date" class="block text-xs font-semibold text-on-surface-variant mb-1">{{ __('Date') }}</label>
                    <input id="date" name="date" type="date" value="{{ $date->toDateString() }}" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm" />
                </div>
            </x-clinic.branch-filter>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalCollected, 2) }}</div>
                <div class="text-sm text-slate-500">{{ __('Total collected') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalPayments) }}</div>
                <div class="text-sm text-slate-500">{{ __('Payments') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalOutstanding, 2) }}</div>
                <div class="text-sm text-slate-500">{{ __('Outstanding') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Branch') }}</th>
                        <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Collected') }}</th>
                        <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Payments') }}</th>
                        <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Outstanding') }}</th>
                        <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Unpaid invoices') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium">{{ $report['branch_name'] }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($report['total_collected'], 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($report['payment_count']) }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($report['outstanding_balance'], 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($report['unpaid_invoice_count']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No branch data for this date.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-clinic-layout>
