<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\RegistrationRequestStatus;
use App\Models\Organization;
use App\Models\OrganizationRegistrationRequest;
use App\Models\User;
use App\Notifications\NewRegistrationRequestNotification;
use App\Notifications\RegistrationRequestApprovedNotification;
use App\Notifications\RegistrationRequestRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

class RegistrationRequestService
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     location: string,
     *     government_approval: string,
     *     contact_person: string,
     *     email: string,
     *     phone: string,
     * }  $data
     */
    public function submit(array $data): OrganizationRegistrationRequest
    {
        $request = OrganizationRegistrationRequest::query()->create([
            ...$data,
            'status' => RegistrationRequestStatus::Pending,
        ]);

        $this->notifySuperAdminsOfNewRequest($request);

        $this->auditLogService->record(
            AuditAction::RegistrationRequestSubmitted,
            __('New registration request from :name.', ['name' => $request->name]),
            $request,
            null,
            ['email' => $request->email],
        );

        return $request;
    }

    public function approve(OrganizationRegistrationRequest $request, User $reviewer): OrganizationRegistrationRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException(__('This request has already been reviewed.'));
        }

        return DB::transaction(function () use ($request, $reviewer): OrganizationRegistrationRequest {
            $request->update([
                'status' => RegistrationRequestStatus::Approved,
                'onboarding_token' => Str::random(64),
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $request->notify(new RegistrationRequestApprovedNotification($request->fresh()));

            $this->auditLogService->record(
                AuditAction::RegistrationRequestApproved,
                __('Approved registration request from :name.', ['name' => $request->name]),
                $request,
                null,
                ['email' => $request->email],
            );

            return $request->fresh();
        });
    }

    public function reject(OrganizationRegistrationRequest $request, User $reviewer, string $reason): OrganizationRegistrationRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException(__('This request has already been reviewed.'));
        }

        return DB::transaction(function () use ($request, $reviewer, $reason): OrganizationRegistrationRequest {
            $request->update([
                'status' => RegistrationRequestStatus::Rejected,
                'rejection_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'onboarding_token' => null,
            ]);

            $request->notify(new RegistrationRequestRejectedNotification($request->fresh()));

            $this->auditLogService->record(
                AuditAction::RegistrationRequestRejected,
                __('Rejected registration request from :name.', ['name' => $request->name]),
                $request,
                null,
                ['email' => $request->email, 'reason' => $reason],
            );

            return $request->fresh();
        });
    }

    public function markCompleted(OrganizationRegistrationRequest $request, Organization $organization): void
    {
        $request->update([
            'status' => RegistrationRequestStatus::Completed,
            'organization_id' => $organization->id,
            'onboarding_token' => null,
        ]);
    }

    public function findByOnboardingToken(string $token): ?OrganizationRegistrationRequest
    {
        return OrganizationRegistrationRequest::query()
            ->where('onboarding_token', $token)
            ->first();
    }

    private function notifySuperAdminsOfNewRequest(OrganizationRegistrationRequest $request): void
    {
        $superAdmins = User::query()
            ->where('platform_role', \App\Enums\PlatformRole::SuperAdmin)
            ->get();

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new NewRegistrationRequestNotification($request));

            return;
        }

        $fallbackEmail = config('audit.alert_email');

        if (filled($fallbackEmail)) {
            Notification::route('mail', $fallbackEmail)
                ->notify(new NewRegistrationRequestNotification($request));
        }
    }
}
