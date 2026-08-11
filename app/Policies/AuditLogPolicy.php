<?php

namespace App\Policies;

use App\Enums\PlatformRole;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->platform_role === PlatformRole::SuperAdmin
            || ($user->platform_role === PlatformRole::OrgAdmin && $user->organizations()->exists());
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if ($user->platform_role === PlatformRole::SuperAdmin) {
            return true;
        }

        if ($auditLog->organization_id === null) {
            return false;
        }

        return $user->organizations()->whereKey($auditLog->organization_id)->exists();
    }
}
