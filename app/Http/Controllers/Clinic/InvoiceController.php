<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $branches = $this->overview->branches($organization);

        $invoices = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) {
                return Invoice::query()
                    ->with('patient')
                    ->latest('issued_at')
                    ->limit(200)
                    ->get()
                    ->map(fn (Invoice $invoice): array => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'total' => $invoice->total,
                        'amount_paid' => $invoice->amount_paid,
                        'status' => $invoice->status,
                        'patient_name' => $invoice->patient?->full_name,
                        'issued_at' => $invoice->issued_at,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                        'branch_url' => TenantPortal::url($branch, '/invoices/'.$invoice->id),
                    ]);
            },
        )->values();

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $invoices->forPage($page, $perPage)->values(),
            $invoices->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('clinic.billing.index', [
            'organization' => $organization,
            'invoices' => $paginator,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'totalInvoices' => $invoices->count(),
            'branchesWithInvoices' => $invoices->pluck('branch_id')->unique()->count(),
        ]);
    }
}
