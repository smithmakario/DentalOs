<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInvoicePaymentRequest;
use App\Models\Invoice;
use App\Services\ClinicActivityLogService;
use App\Services\InvoiceService;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;

class InvoicePaymentController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ClinicActivityLogService $activityLog,
    ) {}

    public function store(StoreInvoicePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('recordPayment', $invoice);

        $attributes = $request->paymentAttributes();
        $payment = $this->invoiceService->recordPayment(
            $invoice,
            $attributes,
            $request->proofFile(),
        );

        $this->activityLog->record(
            'invoice_payment_recorded',
            __('Payment of :amount recorded on :number', [
                'amount' => number_format((float) $attributes['amount'], 2),
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
            [
                'amount' => $attributes['amount'],
                'payment_method' => $attributes['payment_method']?->value ?? $attributes['payment_method'],
                'payment_id' => $payment->id,
                'has_proof' => $payment->hasProof(),
            ],
        );

        $tenant = tenant();

        if ($tenant) {
            return redirect()
                ->to(TenantPortal::absolute($tenant, '/invoices/'.$invoice->id))
                ->with('success', __('Payment recorded successfully.'));
        }

        return redirect()
            ->route('tenant.invoices.show', $invoice)
            ->with('success', __('Payment recorded successfully.'));
    }
}
