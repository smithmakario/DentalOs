<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffMember;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class StaffProvisioningService
{
    public function __construct(
        private StaffAccessService $staffAccessService,
    ) {}

    /**
     * @param  list<string>  $branchIds
     */
    public function sync(StaffMember $staffMember, array $branchIds = []): void
    {
        $staffMember->refresh();

        $targetBranchIds = $staffMember->has_global_branch_access
            ? $staffMember->organization->branches()->pluck('id')->all()
            : $branchIds;

        foreach ($targetBranchIds as $tenantId) {
            $tenant = Tenant::query()->find($tenantId);

            if ($tenant === null || $tenant->organization_id !== $staffMember->organization_id) {
                continue;
            }

            $this->provisionToTenant($staffMember, $tenant);
        }

        $this->deactivateRemovedBranches($staffMember, $targetBranchIds);
    }

    public function provisionToTenant(StaffMember $staffMember, Tenant $tenant): void
    {
        $tenant->run(function () use ($staffMember): void {
            Staff::query()->updateOrCreate(
                ['email' => $staffMember->email],
                [
                    'organization_staff_id' => $staffMember->id,
                    'first_name' => $staffMember->first_name,
                    'last_name' => $staffMember->last_name,
                    'phone' => $staffMember->phone,
                    'role' => $staffMember->role,
                    'specialization' => $staffMember->specialization,
                    'license_number' => $staffMember->license_number,
                    'years_of_experience' => $staffMember->years_of_experience,
                    'avatar_path' => $staffMember->avatar_path,
                    'is_active' => $staffMember->is_active,
                    'password' => $staffMember->password,
                ],
            );
        });
    }

    /**
     * @param  list<string>  $activeBranchIds
     */
    private function deactivateRemovedBranches(StaffMember $staffMember, array $activeBranchIds): void
    {
        $organizationBranchIds = $staffMember->organization->branches()->pluck('id');

        foreach ($organizationBranchIds as $tenantId) {
            if (in_array($tenantId, $activeBranchIds, true)) {
                continue;
            }

            $tenant = Tenant::query()->find($tenantId);

            if ($tenant === null) {
                continue;
            }

            $tenant->run(function () use ($staffMember): void {
                Staff::query()
                    ->where('organization_staff_id', $staffMember->id)
                    ->update(['is_active' => false]);
            });
        }
    }

    /**
     * @param  list<string>  $branchIds
     */
    public function storeAssignments(StaffMember $staffMember, array $branchIds): void
    {
        DB::transaction(function () use ($staffMember, $branchIds): void {
            $staffMember->branchAssignments()->delete();

            foreach ($branchIds as $tenantId) {
                $staffMember->branchAssignments()->create([
                    'tenant_id' => $tenantId,
                ]);
            }

            $this->sync($staffMember, $branchIds);
        });
    }
}
