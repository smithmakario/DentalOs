<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInvoiceRequest;
use App\Http\Requests\Tenant\UpdateInvoiceRequest;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TreatmentPlanOption;
use App\Services\InvoiceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $invoices = Invoice::query()
            ->with('patient')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', function (Builder $query) use ($search): void {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tenant.invoices.index', [
            'invoices' => $invoices,
            'search' => $search,
            'status' => $status,
            'totalOutstanding' => (float) Invoice::query()
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void])
                ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as outstanding')
                ->value('outstanding'),
            'paidThisMonth' => Invoice::query()
                ->where('status', InvoiceStatus::Paid)
                ->where('updated_at', '>=', now()->startOfMonth())
                ->sum('total'),
            'draftCount' => Invoice::where('status', InvoiceStatus::Draft)->count(),
            'overdueCount' => Invoice::query()
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void])
                ->whereNotNull('due_at')
                ->whereDate('due_at', '<', today())
                ->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        $prefill = [
            'patient_id' => $request->integer('patient_id') ?: null,
            'appointment_id' => $request->integer('appointment_id') ?: null,
            'notes' => null,
            'items' => [[
                'description' => '',
                'quantity' => 1,
                'unit_price' => '',
            ]],
        ];

        $optionId = $request->integer('treatment_plan_option_id');
        if ($optionId > 0) {
            $option = TreatmentPlanOption::query()
                ->with(['items', 'treatmentPlan'])
                ->findOrFail($optionId);

            if (! $option->hasConsent()) {
                abort(403, __('Digital consent must be signed before creating an invoice from this treatment option.'));
            }

            $prefill = $this->invoiceService->prefilledFromTreatmentPlanOption($option);
            $prefill['treatment_plan_option_id'] = $option->id;
        }

        return view('tenant.invoices.create', [
            'invoice' => new Invoice([
                'status' => InvoiceStatus::Draft,
                'tax' => 0,
                'discount' => 0,
                'issued_at' => today(),
                'due_at' => today()->addDays(14),
                'patient_id' => $prefill['patient_id'],
                'appointment_id' => $prefill['appointment_id'] ?? null,
                'notes' => $prefill['notes'],
            ]),
            'prefillItems' => $prefill['items'],
            'patients' => Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(),
            'appointments' => Appointment::with('patient')->latest('scheduled_at')->limit(50)->get(),
            'clinicServices' => ClinicService::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->create(
            $request->invoiceAttributes(),
            $request->lineItems(),
        );

        return redirect()
            ->route('tenant.invoices.show', $invoice)
            ->with('success', __('Invoice created successfully.'));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['patient', 'appointment.provider', 'items', 'payments']);

        return view('tenant.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        $this->authorize('update', $invoice);

        if (! $invoice->isEditable()) {
            return redirect()
                ->route('tenant.invoices.show', $invoice)
                ->with('error', __('This invoice can no longer be edited.'));
        }

        $invoice->load('items');

        return view('tenant.invoices.edit', [
            'invoice' => $invoice,
            'patients' => Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(),
            'appointments' => Appointment::with('patient')->latest('scheduled_at')->limit(50)->get(),
            'clinicServices' => ClinicService::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if (! $invoice->isEditable()) {
            return redirect()
                ->route('tenant.invoices.show', $invoice)
                ->with('error', __('This invoice can no longer be edited.'));
        }

        $this->invoiceService->update(
            $invoice,
            $request->invoiceAttributes(),
            $request->lineItems(),
        );

        return redirect()
            ->route('tenant.invoices.show', $invoice)
            ->with('success', __('Invoice updated successfully.'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()
            ->route('tenant.invoices.index')
            ->with('success', __('Invoice deleted successfully.'));
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        $this->authorize('void', $invoice);

        $this->invoiceService->void($invoice);

        return redirect()
            ->route('tenant.invoices.show', $invoice)
            ->with('success', __('Invoice voided.'));
    }
}
