<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPlatformAccess($user);
    }

    public function view(User $user, Organization $organization): bool
    {
        return $this->isSuperAdmin($user) || $this->belongsToOrganization($user, $organization);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, Organization $organization): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->hasOrganizationRole($user, $organization, [
            OrganizationRole::Owner,
            OrganizationRole::Admin,
        ]);
    }

    public function manageBranches(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    private function hasPlatformAccess(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || ($user->platform_role === PlatformRole::OrgAdmin && $user->organizations()->exists());
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->platform_role === PlatformRole::SuperAdmin;
    }

    private function belongsToOrganization(User $user, Organization $organization): bool
    {
        return $user->organizations()->whereKey($organization->id)->exists();
    }

    /**
     * @param  list<OrganizationRole>  $roles
     */
    private function hasOrganizationRole(User $user, Organization $organization, array $roles): bool
    {
        return $user->organizations()
            ->whereKey($organization->id)
            ->wherePivotIn('role', array_map(fn (OrganizationRole $role): string => $role->value, $roles))
            ->exists();
    }
}
