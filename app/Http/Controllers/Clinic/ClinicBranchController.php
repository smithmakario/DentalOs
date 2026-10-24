<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\AuditAction;
use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreClinicBranchRequest;
use App\Services\AuditLogService;
use App\Services\ClinicOnboardingService;
use App\Services\ClinicSubscriptionAccess;
use App\Support\BranchIdentifier;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicBranchController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(
        private ClinicOnboardingService $clinicOnboardingService,
        private ClinicSubscriptionAccess $clinicSubscriptionAccess,
        private AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branches = $organization->branches()->latest()->get();

        return view('clinic.branches.index', [
            'organization' => $organization,
            'branches' => $branches,
            'appHost' => BranchIdentifier::baseHost(),
            'canAddBranch' => $this->clinicSubscriptionAccess->canAddBranch($organization),
        ]);
    }

    public function store(StoreClinicBranchRequest $request): RedirectResponse
    {
        $organization = $this->clinicOrganization($request);

        if (! $this->clinicSubscriptionAccess->canAddBranch($organization)) {
            return redirect()
                ->route('clinic.branches.index')
                ->withErrors(['branch_name' => __('This clinic has reached its branch limit for the current plan.')]);
        }

        $validated = $request->validated();

        $branch = $this->clinicOnboardingService->addBranch($organization, [
            'branch_name' => $validated['branch_name'],
            'branch_slug' => $validated['branch_slug'],
            'contact_email' => $organization->email,
        ]);

        $this->auditLogService->record(
            AuditAction::BranchCreated,
            __('Created branch :name for :clinic.', [
                'name' => $validated['branch_name'],
                'clinic' => $organization->name,
            ]),
            $organization,
            $organization,
            $validated,
        );

        return redirect()
            ->route('clinic.branches.index')
            ->with('status', __('Branch created successfully. Branch staff login: :url', [
                'url' => TenantPortal::loginUrl($branch, $request),
            ]));
    }
}
