<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreStaffMemberRequest;
use App\Http\Requests\Central\UpdateStaffMemberRequest;
use App\Enums\AuditAction;
use App\Models\Organization;
use App\Models\StaffMember;
use App\Services\AuditLogService;
use App\Services\StaffProvisioningService;
use App\Support\StaffRolePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffMemberController extends Controller
{
    public function __construct(
        private StaffProvisioningService $staffProvisioningService,
        private AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', StaffMember::class);

        $user = auth()->user();

        $organizations = $user->isSuperAdmin()
            ? Organization::query()->withCount('staffMembers')->orderBy('name')->get()
            : $user->organizations()->withCount('staffMembers')->orderBy('name')->get();

        return view('central.staff.index', [
            'organizations' => $organizations,
        ]);
    }

    public function organizationIndex(Organization $organization): View
    {
        $this->authorize('view', $organization);
        $this->authorize('viewAny', StaffMember::class);

        $staffMembers = $organization->staffMembers()
            ->with('branchAssignments.branch.domains')
            ->latest()
            ->get();

        return view('central.staff.organization', [
            'organization' => $organization->load('branches.domains'),
            'staffMembers' => $staffMembers,
        ]);
    }

    public function create(Organization $organization): View
    {
        $this->authorize('create', [StaffMember::class, $organization]);

        return view('central.staff.create', [
            'organization' => $organization->load('branches'),
            'roles' => \App\Enums\StaffRole::cases(),
        ]);
    }

    public function store(StoreStaffMemberRequest $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validated();
        $branchIds = $validated['branch_ids'] ?? [];

        $staffMember = $organization->staffMembers()->create(collect($validated)->except('branch_ids')->all());

        if ($staffMember->has_global_branch_access) {
            $this->staffProvisioningService->sync($staffMember);
        } else {
            $this->staffProvisioningService->storeAssignments($staffMember, $branchIds);
        }

        $this->auditLogService->record(
            AuditAction::StaffCreated,
            __('Created staff member :name for :clinic.', [
                'name' => $staffMember->full_name,
                'clinic' => $organization->name,
            ]),
            $staffMember,
            $organization,
            ['role' => $staffMember->role->value, 'email' => $staffMember->email],
        );

        return redirect()
            ->route('clinics.staff.index', $organization)
            ->with('status', __('Staff member created and provisioned to assigned branches.'));
    }

    public function edit(Organization $organization, StaffMember $staffMember): View
    {
        $this->authorize('update', $staffMember);
        abort_unless($staffMember->organization_id === $organization->id, 404);

        $staffMember->load('branchAssignments');

        return view('central.staff.edit', [
            'organization' => $organization->load('branches'),
            'staffMember' => $staffMember,
            'roles' => \App\Enums\StaffRole::cases(),
            'permissionPreview' => StaffRolePermissions::permissionLabels($staffMember->role),
        ]);
    }

    public function update(UpdateStaffMemberRequest $request, Organization $organization, StaffMember $staffMember): RedirectResponse
    {
        $this->authorize('update', $staffMember);
        abort_unless($staffMember->organization_id === $organization->id, 404);

        $validated = $request->validated();
        $branchIds = $validated['branch_ids'] ?? [];

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $staffMember->update(collect($validated)->except('branch_ids')->all());

        if ($staffMember->has_global_branch_access) {
            $staffMember->branchAssignments()->delete();
            $this->staffProvisioningService->sync($staffMember);
        } else {
            $this->staffProvisioningService->storeAssignments($staffMember, $branchIds);
        }

        $this->auditLogService->record(
            AuditAction::StaffUpdated,
            __('Updated staff member :name.', ['name' => $staffMember->full_name]),
            $staffMember,
            $organization,
            ['role' => $staffMember->role->value],
        );

        return redirect()
            ->route('clinics.staff.index', $organization)
            ->with('status', __('Staff member updated.'));
    }

    public function destroy(Organization $organization, StaffMember $staffMember): RedirectResponse
    {
        $this->authorize('delete', $staffMember);
        abort_unless($staffMember->organization_id === $organization->id, 404);

        $staffMember->update(['is_active' => false]);
        $this->staffProvisioningService->sync($staffMember);

        $this->auditLogService->record(
            AuditAction::StaffDeactivated,
            __('Deactivated staff member :name.', ['name' => $staffMember->full_name]),
            $staffMember,
            $organization,
        );

        return redirect()
            ->route('clinics.staff.index', $organization)
            ->with('status', __('Staff member deactivated.'));
    }
}
