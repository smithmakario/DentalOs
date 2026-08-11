<?php

namespace App\Policies;

use App\Enums\PlatformRole;
use App\Models\OrganizationRegistrationRequest;
use App\Models\User;

class OrganizationRegistrationRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->platform_role === PlatformRole::SuperAdmin;
    }

    public function view(User $user, OrganizationRegistrationRequest $organizationRegistrationRequest): bool
    {
        return $user->platform_role === PlatformRole::SuperAdmin;
    }

    public function review(User $user, OrganizationRegistrationRequest $organizationRegistrationRequest): bool
    {
        return $user->platform_role === PlatformRole::SuperAdmin
            && $organizationRegistrationRequest->isPending();
    }
}
