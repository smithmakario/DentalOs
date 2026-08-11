<x-tenant-layout>
    <div class="p-8 max-w-6xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-start mb-8">
            <div>
                <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('tenant.invoices.index') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to billing') }}
                </a>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ $invoice->invoice_number }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    <a class="text-primary hover:underline" href="{{ route('tenant.patients.show', $invoice->patient) }}">{{ $invoice->patient->full_name }}</a>
                    @if ($invoice->issued_at)
                        · {{ __('Issued :date', ['date' => $invoice->issued_at->format('M j, Y')]) }}
                    @endif
                </p>
            </div>
            <div class="flex gap-3">
                @can('update', $invoice)
                    @if ($invoice->isEditable())
                        <a class="flex items-center gap-2 border border-slate-200 text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-slate-50" href="{{ route('tenant.invoices.edit', $invoice) }}">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            {{ __('Edit') }}
                        </a>
                    @endif
                @endcan
                @can('void', $invoice)
                    <form method="POST" action="{{ route('tenant.invoices.void', $invoice) }}" onsubmit="return confirm(@js(__('Void this invoice?')))">
                        @csrf
                        @method('PATCH')
                        <button class="flex items-center gap-2 border border-error/30 text-error px-6 py-2.5 rounded-lg font-label-md hover:bg-error/5" type="submit">
                            <span class="material-symbols-outlined text-[18px]">block</span>
                            {{ __('Void') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Status') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ $invoice->status->label() }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Total') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ number_format($invoice->total, 2) }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Amount Paid') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ number_format($invoice->amount_paid, 2) }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Balance Due') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ number_format($invoice->balanceDue(), 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="font-h3 text-on-surface">{{ __('Line Items') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Description') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant text-right">{{ __('Qty') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant text-right">{{ __('Unit Price') }}</th>
                                    <th class="px-6 py-3 text-label-sm text-on-surface-variant text-right">{{ __('Subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-3 text-on-surface">{{ $item->description }}</td>
                                        <td class="px-6 py-3 text-right">{{ $item->quantity }}</td>
                                        <td class="px-6 py-3 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-3 text-right font-label-md">{{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-200 bg-slate-50">
                                <tr>
                                    <td class="px-6 py-3 text-right text-on-surface-variant" colspan="3">{{ __('Subtotal') }}</td>
                                    <td class="px-6 py-3 text-right font-label-md">{{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if ((float) $invoice->tax > 0)
                                    <tr>
                                        <td class="px-6 py-3 text-right text-on-surface-variant" colspan="3">{{ __('Tax') }}</td>
                                        <td class="px-6 py-3 text-right font-label-md">{{ number_format($invoice->tax, 2) }}</td>
                                    </tr>
                                @endif
                                @if ((float) $invoice->discount > 0)
                                    <tr>
                                        <td class="px-6 py-3 text-right text-on-surface-variant" colspan="3">{{ __('Discount') }}</td>
                                        <td class="px-6 py-3 text-right font-label-md">−{{ number_format($invoice->discount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="px-6 py-3 text-right font-label-md text-on-surface" colspan="3">{{ __('Total') }}</td>
                                    <td class="px-6 py-3 text-right text-lg font-bold text-on-surface">{{ number_format($invoice->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if ($invoice->notes)
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h3 class="font-h3 text-on-surface mb-3">{{ __('Notes') }}</h3>
                        <p class="text-body-md text-on-surface whitespace-pre-line">{{ $invoice->notes }}</p>
                    </div>
                @endif

                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="font-h3 text-on-surface">{{ __('Payment History') }}</h3>
                    </div>
                    @if ($invoice->payments->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($invoice->payments as $payment)
                                <div class="px-6 py-4 flex justify-between items-center gap-4">
                                    <div>
                                        <p class="font-label-md text-on-surface">{{ $payment->payment_number }}</p>
                                        <p class="text-body-sm text-on-surface-variant">
                                            {{ $payment->payment_method->label() }}
                                            @if ($payment->payment_reference)
                                                · {{ $payment->payment_reference }}
                                            @endif
                                        </p>
                                        @if ($payment->notes)
                                            <p class="text-body-sm text-on-surface-variant mt-1">{{ $payment->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-label-md text-on-surface">{{ number_format($payment->amount, 2) }}</p>
                                        <p class="text-body-sm text-outline">{{ $payment->paid_at?->format('M j, Y g:i A') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-10 text-center text-on-surface-variant">{{ __('No payments recorded yet.') }}</div>
                    @endif
                </div>
            </div>

            <div>
                @can('recordPayment', $invoice)
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 sticky top-24">
                        <h3 class="font-h3 text-on-surface mb-4">{{ __('Record Payment') }}</h3>
                        <form method="POST" action="{{ route('tenant.invoices.payments.store', $invoice) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="amount">{{ __('Amount') }}</label>
                                <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg @error('amount') border-error @enderror" id="amount" name="amount" type="number" step="0.01" min="0.01" max="{{ $invoice->balanceDue() }}" value="{{ old('amount', $invoice->balanceDue()) }}" required />
                                @error('amount') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                <p class="mt-1 text-body-sm text-outline">{{ __('Balance due: :amount', ['amount' => number_format($invoice->balanceDue(), 2)]) }}</p>
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="payment_method">{{ __('Payment Method') }}</label>
                                <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg" id="payment_method" name="payment_method" required>
                                    @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                        <option value="{{ $method->value }}" @selected(old('payment_method', $invoice->patient->preferred_payment_method?->value) === $method->value)>{{ $method->label() }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="payment_reference">{{ __('Reference') }}</label>
                                <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg" id="payment_reference" name="payment_reference" type="text" value="{{ old('payment_reference') }}" placeholder="{{ __('Receipt #, transaction ID, etc.') }}" />
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="paid_at">{{ __('Paid At') }}</label>
                                <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg" id="paid_at" name="paid_at" type="datetime-local" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" />
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="payment_notes">{{ __('Notes') }}</label>
                                <textarea class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg" id="payment_notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            </div>
                            @if ($invoice->patient->usesHmo())
                                <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg text-body-sm text-on-surface">
                                    <p class="font-label-md text-on-surface mb-1">{{ __('Patient HMO') }}</p>
                                    <p>{{ $invoice->patient->insurance_provider }} · {{ $invoice->patient->hmo_plan }}</p>
                                    <p class="text-on-surface-variant">{{ __('Member ID') }}: {{ $invoice->patient->insurance_number }}</p>
                                </div>
                            @endif
                            <button class="w-full flex items-center justify-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90" type="submit">
                                <span class="material-symbols-outlined text-[18px]">payments</span>
                                {{ __('Record Payment') }}
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</x-tenant-layout>
