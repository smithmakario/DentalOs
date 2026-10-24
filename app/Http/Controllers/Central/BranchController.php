<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreBranchRequest;
use App\Http\Requests\Central\UpdateBranchPrimaryAdminRequest;
use App\Http\Requests\Central\UpdateBranchRequest;
use App\Models\BranchProfile;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\AuditLogService;
use App\Services\ClinicOnboardingService;
use App\Services\ClinicPrimaryAdminService;
use App\Support\BranchIdentifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(
        private ClinicOnboardingService $clinicOnboardingService,
        private ClinicPrimaryAdminService $clinicPrimaryAdminService,
        private AuditLogService $auditLogService,
    ) {}

    public function index(Organization $organization): View
    {
        $this->authorize('manageBranches', $organization);

        $branches = $organization->branches()
            ->latest()
            ->get();

        $branchAdmins = $branches->mapWithKeys(
            fn (Tenant $branch): array => [
                (string) $branch->getTenantKey() => $this->clinicPrimaryAdminService->branchPrimaryAdmin($branch),
            ],
        );

        return view('central.clinics.branches.index', [
            'organization' => $organization,
            'branches' => $branches,
            'branchAdmins' => $branchAdmins,
            'appHost' => BranchIdentifier::baseHost(),
        ]);
    }

    public function store(StoreBranchRequest $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validated();

        $this->clinicOnboardingService->addBranch($organization, [
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
            ->route('clinics.branches.index', $organization)
            ->with('status', __('Branch created successfully.'));
    }

    public function update(
        UpdateBranchRequest $request,
        Organization $organization,
        Tenant $tenant,
    ): RedirectResponse {
        $this->ensureBranchBelongsToOrganization($organization, $tenant);

        $validated = $request->validated();

        $tenant->update([
            'name' => $validated['branch_name'],
            'is_active' => $validated['is_active'],
        ]);

        $tenant->run(function () use ($validated): void {
            BranchProfile::query()->first()?->update([
                'name' => $validated['branch_name'],
            ]);
        });

        $this->auditLogService->record(
            AuditAction::BranchUpdated,
            __('Updated branch :name for :clinic.', [
                'name' => $validated['branch_name'],
                'clinic' => $organization->name,
            ]),
            $organization,
            $organization,
            $validated,
        );

        return redirect()
            ->route('clinics.branches.index', $organization)
            ->with('status', __('Branch updated successfully.'));
    }

    public function updatePrimaryAdmin(
        UpdateBranchPrimaryAdminRequest $request,
        Organization $organization,
        Tenant $tenant,
    ): RedirectResponse {
        $this->ensureBranchBelongsToOrganization($organization, $tenant);

        $this->clinicPrimaryAdminService->updateBranchPrimaryAdmin(
            $organization,
            $tenant,
            $request->validated(),
        );

        $this->auditLogService->record(
            AuditAction::BranchUpdated,
            __('Updated primary admin for branch :name.', ['name' => $tenant->name]),
            $organization,
            $organization,
            [
                'branch_id' => $tenant->getTenantKey(),
                'admin_email' => $request->validated('admin_email'),
            ],
        );

        return redirect()
            ->route('clinics.branches.index', $organization)
            ->with('status', __('Branch primary admin updated successfully.'));
    }

    public function destroy(Organization $organization, Tenant $tenant): RedirectResponse
    {
        $this->authorize('manageBranches', $organization);
        $this->ensureBranchBelongsToOrganization($organization, $tenant);

        if ($organization->branches()->count() <= 1) {
            return redirect()
                ->route('clinics.branches.index', $organization)
                ->withErrors(['branch' => __('A clinic must keep at least one branch.')]);
        }

        $branchId = $tenant->id;
        $branchName = $tenant->name;

        $tenant->delete();

        $this->auditLogService->record(
            AuditAction::BranchDeleted,
            __('Deleted branch :name for :clinic.', [
                'name' => $branchName,
                'clinic' => $organization->name,
            ]),
            $organization,
            $organization,
            ['branch_id' => $branchId],
        );

        return redirect()
            ->route('clinics.branches.index', $organization)
            ->with('status', __('Branch deleted successfully.'));
    }

    private function ensureBranchBelongsToOrganization(Organization $organization, Tenant $tenant): void
    {
        abort_unless(
            (int) $tenant->organization_id === (int) $organization->id,
            404,
        );
    }
}
