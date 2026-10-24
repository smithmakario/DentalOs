@php use App\Enums\PaymentMethod; @endphp
<x-tenant-layout>
    <div class="p-8 max-w-6xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Daily Reconciliation') }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    {{ __('Collections by payment method, discounts, and outstanding balances for :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            <a href="{{ route('tenant.reconciliation', ['date' => $date->format('Y-m-d'), 'export' => 'csv']) }}" class="flex items-center gap-2 border border-slate-200 px-5 py-2.5 rounded-lg font-label-md hover:bg-slate-50">
                <span class="material-symbols-outlined text-[18px]">download</span>
                {{ __('Export CSV') }}
            </a>
        </div>

        <form method="GET" action="{{ route('tenant.reconciliation') }}" class="bg-white border border-slate-200 rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-label-sm text-on-surface-variant mb-1" for="recon_date">{{ __('Date') }}</label>
                <input id="recon_date" name="date" type="date" value="{{ $date->format('Y-m-d') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm" />
            </div>
            <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90">{{ __('View') }}</button>
            <a href="{{ route('tenant.reconciliation', ['date' => today()->format('Y-m-d')]) }}" class="px-5 py-2 border border-slate-200 rounded-lg font-label-md hover:bg-slate-50">{{ __('Today') }}</a>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Collected') }}</div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($report['total_collected'], 2) }}</div>
                <div class="text-body-sm text-outline mt-1">{{ $report['payment_count'] }} {{ __('payments') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Discounts') }}</div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($report['discounts_applied'], 2) }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Outstanding') }}</div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($report['outstanding_balance'], 2) }}</div>
                <div class="text-body-sm text-outline mt-1">{{ $report['unpaid_invoice_count'] }} {{ __('open invoices') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Voided today') }}</div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($report['voided_total'], 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('By payment method') }}</h3>
                <dl class="space-y-3">
                    @foreach (PaymentMethod::cases() as $method)
                        <div class="flex justify-between gap-4">
                            <dt class="text-body-md text-on-surface-variant">{{ $method->label() }}</dt>
                            <dd class="font-semibold text-on-surface">{{ number_format((float) ($report['collections_by_method'][$method->value] ?? 0), 2) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-h3 text-on-surface">{{ __('Payments') }}</h3>
                </div>
                @if ($report['payments']->isEmpty())
                    <div class="px-6 py-12 text-center text-on-surface-variant">{{ __('No payments recorded for this date.') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Time') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Patient') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Invoice') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Method') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant text-right">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($report['payments'] as $payment)
                                    <tr>
                                        <td class="px-6 py-3 text-body-sm">{{ $payment->paid_at?->format('g:i A') ?? '—' }}</td>
                                        <td class="px-6 py-3">{{ $payment->invoice?->patient?->full_name ?? '—' }}</td>
                                        <td class="px-6 py-3">
                                            @if ($payment->invoice)
                                                <a class="text-primary hover:underline" href="{{ route('tenant.invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-3">{{ $payment->payment_method?->label() ?? '—' }}</td>
                                        <td class="px-6 py-3 text-right font-semibold">{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-tenant-layout>
