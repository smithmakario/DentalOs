<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInvoicePaymentRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;

class InvoicePaymentController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function store(StoreInvoicePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('recordPayment', $invoice);

        $this->invoiceService->recordPayment(
            $invoice,
            $request->paymentAttributes(),
        );

        return redirect()
            ->route('tenant.invoices.show', $invoice)
            ->with('success', __('Payment recorded successfully.'));
    }
}
