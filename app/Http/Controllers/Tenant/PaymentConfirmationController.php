<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ClinicActivityLogService;
use App\Services\InvoiceService;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;

class PaymentConfirmationController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ClinicActivityLogService $activityLog,
    ) {}

    public function store(Invoice $invoice, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        $this->authorize('confirmPayment', $invoice);

        $staff = auth('staff')->user();
        abort_unless($staff !== null, 403);

        if ($payment->isConfirmed()) {
            return $this->redirectToInvoice($invoice)
                ->with('success', __('This payment is already confirmed.'));
        }

        $this->invoiceService->confirmPayment($payment, $staff);

        $this->activityLog->record(
            'invoice_payment_confirmed',
            __('Payment :number confirmed on :invoice', [
                'number' => $payment->payment_number,
                'invoice' => $invoice->invoice_number,
            ]),
            $invoice,
            ['payment_id' => $payment->id],
        );

        return $this->redirectToInvoice($invoice)
            ->with('success', __('Payment confirmed.'));
    }

    private function redirectToInvoice(Invoice $invoice): RedirectResponse
    {
        $tenant = tenant();

        if ($tenant) {
            return redirect()->to(TenantPortal::absolute($tenant, '/invoices/'.$invoice->id));
        }

        return redirect()->route('tenant.invoices.show', $invoice);
    }
}
