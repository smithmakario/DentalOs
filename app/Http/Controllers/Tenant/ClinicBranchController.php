<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreClinicBranchRequest;
use App\Services\AuditLogService;
use App\Services\ClinicAdminAccess;
use App\Services\ClinicOnboardingService;
use App\Services\ClinicSubscriptionAccess;
use App\Support\BranchIdentifier;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClinicBranchController extends Controller
{
    public function __construct(
        private ClinicAdminAccess $clinicAdminAccess,
        private ClinicOnboardingService $clinicOnboardingService,
        private ClinicSubscriptionAccess $clinicSubscriptionAccess,
        private AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        abort_unless($this->clinicAdminAccess->canManageClinic(), 403);

        $organization = $this->clinicAdminAccess->organization();
        abort_unless($organization !== null, 404);

        $branches = $organization->branches()->latest()->get();

        return view('tenant.clinic-branches.index', [
            'organization' => $organization,
            'branches' => $branches,
            'appHost' => BranchIdentifier::baseHost(),
            'canAddBranch' => $this->clinicSubscriptionAccess->canAddBranch($organization),
        ]);
    }

    public function store(StoreClinicBranchRequest $request): RedirectResponse
    {
        $organization = $this->clinicAdminAccess->organization();
        abort_unless($organization !== null, 404);

        if (! $this->clinicSubscriptionAccess->canAddBranch($organization)) {
            return redirect()
                ->route('tenant.clinic-branches.index')
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
            ->route('tenant.clinic-branches.index')
            ->with('status', __('Branch created successfully. Staff login: :url', [
                'url' => TenantPortal::loginUrl($branch, $request),
            ]));
    }
}
