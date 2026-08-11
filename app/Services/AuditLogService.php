<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\AuditEventAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        AuditAction $action,
        string $description,
        ?Model $subject = null,
        ?Organization $organization = null,
        array $properties = [],
        ?Request $request = null,
    ): AuditLog {
        $user = Auth::user();
        $request ??= request();

        $auditLog = AuditLog::query()->create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'organization_id' => $organization?->id ?? ($subject instanceof Organization ? $subject->id : null),
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($action->shouldAlert()) {
            $this->notifySuperAdmins($auditLog);
        }

        return $auditLog;
    }

    /**
     * @return list<int>
     */
    public function organizationIdsFor(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return Organization::query()->pluck('id')->all();
        }

        return $user->organizations()->pluck('organizations.id')->all();
    }

    private function notifySuperAdmins(AuditLog $auditLog): void
    {
        $superAdmins = User::query()
            ->where('platform_role', \App\Enums\PlatformRole::SuperAdmin)
            ->get();

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new AuditEventAlert($auditLog));

            return;
        }

        $fallbackEmail = config('audit.alert_email');

        if (filled($fallbackEmail)) {
            Notification::route('mail', $fallbackEmail)
                ->notify(new AuditEventAlert($auditLog));
        }
    }
}
