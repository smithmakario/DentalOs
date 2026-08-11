<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Billing & Invoices') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Track patient invoices, payments, and outstanding balances for :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            @can('create', App\Models\Invoice::class)
                <a href="{{ route('tenant.invoices.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    {{ __('New Invoice') }}
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format((float) $totalOutstanding, 2) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Outstanding') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format((float) $paidThisMonth, 2) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Paid This Month') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($draftCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Draft Invoices') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($overdueCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Overdue') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <form action="{{ route('tenant.invoices.index') }}" class="flex flex-wrap gap-3 items-center" method="GET">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-56 focus:ring-2 focus:ring-primary focus:border-transparent outline-none" name="search" type="search" value="{{ $search }}" placeholder="{{ __('Search invoices or patients') }}" />
                    </div>
                    <select class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" name="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (\App\Enums\InvoiceStatus::cases() as $invoiceStatus)
                            <option value="{{ $invoiceStatus->value }}" @selected($status === $invoiceStatus->value)>{{ $invoiceStatus->label() }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-medium hover:opacity-90" type="submit">{{ __('Filter') }}</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Invoice') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Issued') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant text-right">{{ __('Total') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant text-right">{{ __('Balance') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoices as $invoice)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a class="font-label-md text-primary hover:underline" href="{{ route('tenant.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                </td>
                                <td class="px-6 py-4">
                                    <a class="text-on-surface hover:underline" href="{{ route('tenant.patients.show', $invoice->patient) }}">{{ $invoice->patient->full_name }}</a>
                                </td>
                                <td class="px-6 py-4 text-body-sm text-on-surface-variant">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right font-label-md text-on-surface">{{ number_format($invoice->total, 2) }}</td>
                                <td class="px-6 py-4 text-right font-label-md text-on-surface">{{ number_format($invoice->balanceDue(), 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $invoice->status->label() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-on-surface-variant" colspan="6">{{ __('No invoices yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="p-6 border-t border-slate-100">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-tenant-layout>
