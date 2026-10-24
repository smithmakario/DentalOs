<?php

namespace App\Services;

use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\Tenant;
use App\Support\ClinicPortal;

class ClinicAdminAccess
{
    public function organization(?Tenant $tenant = null): ?Organization
    {
        $tenant ??= tenant();

        if (! $tenant instanceof Tenant) {
            return null;
        }

        $tenant->loadMissing('organization');

        return $tenant->organization;
    }

    public function isAdminBranch(?Tenant $tenant = null): bool
    {
        $tenant ??= tenant();
        $organization = $this->organization($tenant);

        if ($tenant === null || $organization === null || $organization->admin_branch_id === null) {
            return false;
        }

        return (string) $organization->admin_branch_id === (string) $tenant->getTenantKey();
    }

    public function canManageClinic(?Staff $staff = null, ?Tenant $tenant = null): bool
    {
        $staff ??= auth('staff')->user();
        $tenant ??= tenant();

        if (! $staff instanceof Staff || ! $tenant instanceof Tenant) {
            return false;
        }

        if ($staff->role !== StaffRole::ClinicAdmin) {
            return false;
        }

        $organization = $this->organization($tenant);

        if ($organization === null) {
            return false;
        }

        // Clinic-portal session means this staff signed in as the primary admin.
        if (ClinicPortal::isActive() && ClinicPortal::organizationId() === $organization->id) {
            return true;
        }

        // On the HQ / admin branch, ClinicAdmin is the primary admin.
        if ($this->isAdminBranch($tenant)) {
            return true;
        }

        // On another branch: only the synced primary admin (same email as HQ) may manage clinic-wide.
        return $this->matchesAdminBranchPrimaryEmail($organization, $staff->email);
    }

    public function matchesAdminBranchPrimaryEmail(Organization $organization, string $email): bool
    {
        $adminBranch = $organization->adminBranch;

        if (! $adminBranch instanceof Tenant) {
            return false;
        }

        try {
            $primaryEmail = $adminBranch->run(function (): ?string {
                return Staff::query()
                    ->where('role', StaffRole::ClinicAdmin)
                    ->orderBy('id')
                    ->value('email');
            });
        } catch (\Throwable) {
            return false;
        }

        return is_string($primaryEmail)
            && strtolower($primaryEmail) === strtolower($email);
    }
}
