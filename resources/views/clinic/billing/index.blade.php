<x-clinic-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="font-h1 text-on-surface mb-2">{{ __('Billing') }}</h2>
            <p class="text-on-surface-variant">{{ __('Invoices across all branches.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($totalInvoices) }}</div>
                <div class="text-sm text-slate-500">{{ __('Invoices') }}</div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold">{{ number_format($branchesWithInvoices) }}</div>
                <div class="text-sm text-slate-500">{{ __('Branches with invoices') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <x-clinic.branch-filter :action="route('clinic.billing.index')" :branches="$branches" :selected-branch="$selectedBranch" />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Invoice') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Patient') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Branch') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Total') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-xs uppercase text-slate-500 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium">{{ $invoice['invoice_number'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ $invoice['patient_name'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $invoice['branch_name'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ number_format((float) $invoice['total'], 2) }}</td>
                                <td class="px-6 py-4 text-sm">{{ is_object($invoice['status']) ? $invoice['status']->value : $invoice['status'] }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $invoice['branch_url'] }}" class="text-sm text-primary hover:underline">{{ __('Open at branch') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No invoices found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="p-4 border-t">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-clinic-layout>
