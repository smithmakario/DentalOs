<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Models\TreatmentPlan;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class TreatmentPlanController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $branches = $this->overview->branches($organization);

        $plans = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) {
                return TreatmentPlan::query()
                    ->with(['patient', 'provider'])
                    ->latest()
                    ->limit(200)
                    ->get()
                    ->map(fn (TreatmentPlan $plan): array => [
                        'id' => $plan->id,
                        'title' => $plan->title ?? __('Treatment plan #:id', ['id' => $plan->id]),
                        'status' => $plan->status,
                        'patient_name' => $plan->patient?->full_name,
                        'provider_name' => $plan->provider?->full_name,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                        'branch_url' => TenantPortal::url($branch, '/treatment-plans/'.$plan->id),
                    ]);
            },
        )->values();

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $plans->forPage($page, $perPage)->values(),
            $plans->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('clinic.treatments.index', [
            'organization' => $organization,
            'plans' => $paginator,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'totalPlans' => $plans->count(),
            'branchesWithPlans' => $plans->pluck('branch_id')->unique()->count(),
        ]);
    }
}
