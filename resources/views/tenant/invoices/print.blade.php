<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{{ __('Invoice') }} {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; color: #0f172a; margin: 0; padding: 32px; background: #fff; }
        .meta { color: #475569; font-size: 14px; }
        h1 { font-size: 28px; margin: 0 0 4px; }
        h2 { font-size: 18px; margin: 24px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: .04em; }
        .right { text-align: right; }
        .totals { margin-top: 16px; width: 280px; margin-left: auto; }
        .totals td { border: 0; padding: 6px 0; }
        .totals .grand { font-size: 18px; font-weight: 700; }
        .actions { margin-bottom: 24px; }
        .actions button, .actions a { font-family: system-ui, sans-serif; margin-right: 8px; padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; text-decoration: none; color: #0f172a; cursor: pointer; }
        @media print { .actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">{{ __('Print / Save PDF') }}</button>
        <a href="{{ route('tenant.invoices.show', $invoice) }}">{{ __('Back to invoice') }}</a>
    </div>

    <header style="display:flex;justify-content:space-between;gap:24px;margin-bottom:32px;">
        <div>
            <h1>{{ $branch?->name ?? tenant('name') ?? config('app.name') }}</h1>
            <p class="meta">
                @if ($branch?->address) {{ $branch->address }}<br> @endif
                @if ($branch?->phone) {{ $branch->phone }} · @endif
                {{ $branch?->contact_email }}
            </p>
        </div>
        <div style="text-align:right;">
            <h1>{{ __('Invoice') }}</h1>
            <p class="meta">
                {{ $invoice->invoice_number }}<br>
                {{ __('Status') }}: {{ $invoice->status->label() }}<br>
                @if ($invoice->issued_at) {{ __('Issued') }}: {{ $invoice->issued_at->format('M j, Y') }}<br> @endif
                @if ($invoice->due_at) {{ __('Due') }}: {{ $invoice->due_at->format('M j, Y') }} @endif
            </p>
        </div>
    </header>

    <section>
        <h2>{{ __('Bill to') }}</h2>
        <p>
            <strong>{{ $invoice->patient->full_name }}</strong><br>
            @if ($invoice->patient->patient_id_string) {{ __('ID') }}: {{ $invoice->patient->patient_id_string }}<br> @endif
            {{ $invoice->patient->phone }}<br>
            {{ $invoice->patient->email }}
        </p>
    </section>

    <table>
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th class="right">{{ __('Qty') }}</th>
                <th class="right">{{ __('Unit') }}</th>
                <th class="right">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>{{ __('Subtotal') }}</td>
            <td class="right">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Tax') }}</td>
            <td class="right">{{ number_format($invoice->tax, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Discount') }}</td>
            <td class="right">{{ number_format($invoice->discount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('Total') }}</td>
            <td class="right">{{ number_format($invoice->total, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Paid') }}</td>
            <td class="right">{{ number_format($invoice->amount_paid, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('Balance due') }}</td>
            <td class="right">{{ number_format($invoice->balanceDue(), 2) }}</td>
        </tr>
    </table>

    @if ($invoice->payments->isNotEmpty())
        <h2>{{ __('Payments') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th class="right">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('M j, Y g:i A') }}</td>
                        <td>{{ $payment->payment_method?->label() }}</td>
                        <td>{{ $payment->payment_reference ?? '—' }}</td>
                        <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($invoice->notes)
        <h2>{{ __('Notes') }}</h2>
        <p class="meta">{{ $invoice->notes }}</p>
    @endif
</body>
</html>
