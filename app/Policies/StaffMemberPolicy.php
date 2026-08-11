<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\StaffMember;
use App\Models\User;

class StaffMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPlatformAccess($user);
    }

    public function view(User $user, StaffMember $staffMember): bool
    {
        return $this->canManageOrganizationStaff($user, $staffMember->organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        return $this->canManageOrganizationStaff($user, $organization);
    }

    public function update(User $user, StaffMember $staffMember): bool
    {
        return $this->canManageOrganizationStaff($user, $staffMember->organization);
    }

    public function delete(User $user, StaffMember $staffMember): bool
    {
        return $this->canManageOrganizationStaff($user, $staffMember->organization);
    }

    private function hasPlatformAccess(User $user): bool
    {
        return $user->platform_role === PlatformRole::SuperAdmin
            || ($user->platform_role === PlatformRole::OrgAdmin && $user->organizations()->exists());
    }

    private function canManageOrganizationStaff(User $user, Organization $organization): bool
    {
        if ($user->platform_role === PlatformRole::SuperAdmin) {
            return true;
        }

        return $user->organizations()
            ->whereKey($organization->id)
            ->wherePivotIn('role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
            ])
            ->exists();
    }
}
