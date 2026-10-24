<?php

namespace App\Services;

use App\Models\StaffMember;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class StaffAccessService
{
    /**
     * @return Collection<int, Tenant>
     */
    public function accessibleBranches(StaffMember $staffMember): Collection
    {
        $staffMember->loadMissing('organization.branches.domains');

        if ($staffMember->has_global_branch_access) {
            return $staffMember->organization->branches;
        }

        return Tenant::query()
            ->with('domains')
            ->whereIn('id', $staffMember->branchAssignments()->pluck('tenant_id'))
            ->get();
    }

    public function canAccessTenant(StaffMember $staffMember, string $tenantId): bool
    {
        if (! $staffMember->is_active) {
            return false;
        }

        $tenant = Tenant::query()->find($tenantId);

        if ($tenant === null || $tenant->organization_id !== $staffMember->organization_id) {
            return false;
        }

        if ($staffMember->has_global_branch_access) {
            return true;
        }

        return $staffMember->branchAssignments()
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    public function findMemberForTenantStaff(?int $organizationStaffId, string $email): ?StaffMember
    {
        // StaffMember uses CentralConnection — do not wrap in tenancy()->central().
        // Ending tenancy here leaves subsequent controller queries on the central DB
        // (e.g. Staff::loadCount against dental_os.appointments).
        if ($organizationStaffId !== null) {
            return StaffMember::query()->find($organizationStaffId);
        }

        return StaffMember::query()->where('email', $email)->first();
    }
}
