<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Services\ClinicOverviewService;
use App\Services\DailyReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyReconciliationController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(
        private ClinicOverviewService $overview,
        private DailyReconciliationService $reconciliation,
    ) {}

    public function __invoke(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $date = Carbon::parse($request->string('date', now()->toDateString())->toString())->startOfDay();
        $branches = $this->overview->branches($organization);

        $reports = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) use ($date) {
                $report = $this->reconciliation->forDate($date);

                return collect([[
                    'branch_id' => $branch->getTenantKey(),
                    'branch_name' => $branch->name,
                    'total_collected' => $report['total_collected'],
                    'payment_count' => $report['payment_count'],
                    'outstanding_balance' => $report['outstanding_balance'],
                    'unpaid_invoice_count' => $report['unpaid_invoice_count'],
                    'voided_total' => $report['voided_total'],
                    'collections_by_method' => $report['collections_by_method'],
                ]]);
            },
        );

        return view('clinic.reconciliation.show', [
            'organization' => $organization,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'date' => $date,
            'reports' => $reports,
            'totalCollected' => $reports->sum('total_collected'),
            'totalPayments' => $reports->sum('payment_count'),
            'totalOutstanding' => $reports->sum('outstanding_balance'),
        ]);
    }
}
