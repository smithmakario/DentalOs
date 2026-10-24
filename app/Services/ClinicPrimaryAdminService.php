<?php

namespace App\Services;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\StaffMember;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantPortal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClinicPrimaryAdminService
{
    public function __construct(
        private StaffProvisioningService $staffProvisioningService,
    ) {}

    /**
     * @param  array{
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password?: string|null,
     * }  $data
     */
    public function updateClinicPrimaryAdmin(Organization $organization, array $data): User
    {
        $admin = $this->resolveClinicPrimaryAdmin($organization);
        $previousEmail = $admin?->email;
        $password = filled($data['admin_password'] ?? null)
            ? $data['admin_password']
            : null;

        $this->ensureEmailIsAvailable($data['admin_email'], $admin?->id);

        $admin = DB::transaction(function () use ($organization, $admin, $data, &$password): User {
            if ($admin === null) {
                $password ??= str()->password(16);

                $admin = User::query()->create([
                    'name' => $data['admin_name'],
                    'email' => $data['admin_email'],
                    'password' => $password,
                    'platform_role' => PlatformRole::OrgAdmin,
                    'email_verified_at' => now(),
                ]);

                $organization->users()->attach($admin->id, [
                    'role' => OrganizationRole::Owner->value,
                ]);
            } else {
                $attributes = [
                    'name' => $data['admin_name'],
                    'email' => $data['admin_email'],
                    'platform_role' => PlatformRole::OrgAdmin,
                ];

                if ($password !== null) {
                    $attributes['password'] = $password;
                }

                $admin->update($attributes);

                if (! $organization->users()->whereKey($admin->id)->exists()) {
                    $organization->users()->attach($admin->id, [
                        'role' => OrganizationRole::Owner->value,
                    ]);
                } else {
                    $organization->users()->updateExistingPivot($admin->id, [
                        'role' => OrganizationRole::Owner->value,
                    ]);
                }
            }

            return $admin->fresh();
        });

        $adminBranch = $organization->adminBranch;

        if ($adminBranch instanceof Tenant) {
            $this->syncBranchClinicAdmin(
                $adminBranch,
                $data['admin_name'],
                $data['admin_email'],
                $password,
                $previousEmail,
            );

            // Keep the same primary admin on every branch so they can operate clinic-wide.
            app(PrimaryClinicAdminProvisioner::class)->syncToAllBranches($organization->fresh());
        }

        $this->syncCentralStaffMember(
            $organization->fresh(),
            $admin,
            $previousEmail,
            $password,
        );

        return $admin;
    }

    /**
     * Ensure the clinic primary admin appears in the central staff directory
     * (super-admin clinic staff list) with access to every branch.
     */
    public function ensureListedAsStaff(Organization $organization): ?StaffMember
    {
        $admin = $this->resolveClinicPrimaryAdmin($organization);

        if ($admin === null) {
            return null;
        }

        return $this->syncCentralStaffMember($organization, $admin);
    }

    public function syncCentralStaffMember(
        Organization $organization,
        User $admin,
        ?string $previousEmail = null,
        ?string $plainPassword = null,
    ): StaffMember {
        [$firstName, $lastName] = $this->splitName($admin->name);

        $memberQuery = $organization->staffMembers()
            ->where('role', StaffRole::ClinicAdmin)
            ->orderBy('id');

        $staffMember = null;

        if ($previousEmail !== null) {
            $staffMember = (clone $memberQuery)->where('email', $previousEmail)->first();
        }

        $staffMember ??= (clone $memberQuery)->where('email', $admin->email)->first();
        $staffMember ??= (clone $memberQuery)->where('has_global_branch_access', true)->first();

        $attributes = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $admin->email,
            'role' => StaffRole::ClinicAdmin,
            'has_global_branch_access' => true,
            'is_active' => true,
        ];

        if (filled($plainPassword)) {
            $attributes['password'] = $plainPassword;
        } elseif ($staffMember === null) {
            // Already hashed on the platform user — `hashed` cast will not re-hash.
            $attributes['password'] = $admin->getAttributes()['password'] ?? str()->password(32);
        }

        if ($staffMember === null) {
            $staffMember = $organization->staffMembers()->create($attributes);
        } else {
            $staffMember->update($attributes);
        }

        $staffMember->branchAssignments()->delete();
        $this->staffProvisioningService->sync($staffMember->fresh());

        return $staffMember->fresh(['branchAssignments.branch.domains']);
    }

    /**
     * @param  array{
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password?: string|null,
     * }  $data
     */
    public function updateBranchPrimaryAdmin(Organization $organization, Tenant $tenant, array $data): void
    {
        abort_unless((int) $tenant->organization_id === (int) $organization->id, 404);

        // A clinic has a single primary admin, owned by the admin (HQ) branch.
        // Satellite branches receive a synced copy — they do not get a separate Clinic Admin.
        if (! $organization->isAdminBranch($tenant)) {
            throw ValidationException::withMessages([
                'admin_email' => __('Clinic admin can only be set on the primary clinic branch.'),
            ]);
        }

        $this->updateClinicPrimaryAdmin($organization, $data);
    }

    /**
     * Promote a staff member on the admin branch to the clinic's single primary admin.
     * Other Clinic Admin staff on that branch are demoted to receptionist.
     *
     * Safe to call from an already-initialized tenant request: the original
     * tenancy context (and path URL root) is always restored afterwards.
     */
    public function promoteStaffToClinicPrimary(Organization $organization, Staff $staff, ?string $plainPassword = null): void
    {
        $adminBranch = $organization->adminBranch;

        if (! $adminBranch instanceof Tenant || ! $organization->isAdminBranch($adminBranch)) {
            throw ValidationException::withMessages([
                'role' => __('Clinic admin can only be assigned on the primary clinic branch.'),
            ]);
        }

        if ($staff->role !== StaffRole::ClinicAdmin) {
            return;
        }

        $this->withRestoredTenancy(function () use ($organization, $staff, $plainPassword, $adminBranch): void {
            // Prefer in-context demotion when we are already on the admin branch
            // so we do not churn tenancy/URL root for a same-tenant update.
            $demote = function () use ($staff): void {
                Staff::query()
                    ->where('role', StaffRole::ClinicAdmin)
                    ->whereKeyNot($staff->id)
                    ->update(['role' => StaffRole::Receptionist]);
            };

            if (tenancy()->initialized && (string) tenant('id') === (string) $adminBranch->getTenantKey()) {
                $demote();
            } else {
                $adminBranch->run($demote);
            }

            $this->updateClinicPrimaryAdmin($organization, [
                'admin_name' => $staff->full_name,
                'admin_email' => $staff->email,
                'admin_password' => $plainPassword,
            ]);
        });
    }

    /**
     * Run a callback that may switch tenants, then restore the caller's tenancy
     * context and path-based URL root.
     */
    private function withRestoredTenancy(callable $callback): mixed
    {
        $originalTenant = tenant();

        try {
            return $callback();
        } finally {
            if ($originalTenant instanceof Tenant) {
                if (! tenancy()->initialized
                    || (string) tenant('id') !== (string) $originalTenant->getTenantKey()) {
                    tenancy()->initialize($originalTenant);
                }

                TenantPortal::applyUrlRootForCurrentTenant(
                    app()->bound('request') ? request() : null,
                );
            }
        }
    }

    /**
     * @return array{name: string, email: string}|null
     */
    public function branchPrimaryAdmin(Tenant $tenant): ?array
    {
        return $tenant->run(function (): ?array {
            $staff = Staff::query()
                ->where('role', StaffRole::ClinicAdmin)
                ->orderBy('id')
                ->first();

            if ($staff === null) {
                return null;
            }

            return [
                'name' => $staff->full_name,
                'email' => $staff->email,
            ];
        });
    }

    public function resolveClinicPrimaryAdmin(Organization $organization): ?User
    {
        $organization->loadMissing('users');

        return $organization->users
            ->sortBy(fn (User $user): int => match ($user->pivot->role) {
                OrganizationRole::Owner => 0,
                OrganizationRole::Admin => 1,
                default => 2,
            })
            ->first(fn (User $user): bool => in_array(
                $user->pivot->role,
                [OrganizationRole::Owner, OrganizationRole::Admin],
                true,
            ));
    }

    private function syncBranchClinicAdmin(
        Tenant $tenant,
        string $adminName,
        string $adminEmail,
        ?string $adminPassword,
        ?string $previousEmail,
    ): void {
        [$firstName, $lastName] = $this->splitName($adminName);

        $tenant->run(function () use ($firstName, $lastName, $adminEmail, $adminPassword, $previousEmail): void {
            $staffQuery = Staff::query()->where('role', StaffRole::ClinicAdmin)->orderBy('id');

            $staff = null;

            if ($previousEmail !== null) {
                $staff = (clone $staffQuery)->where('email', $previousEmail)->first();
            }

            $staff ??= $staffQuery->first();

            $attributes = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $adminEmail,
                'role' => StaffRole::ClinicAdmin,
                'is_active' => true,
            ];

            if (filled($adminPassword)) {
                // Plain text — Staff uses the `hashed` cast.
                $attributes['password'] = $adminPassword;
            }

            if ($staff === null) {
                if (! filled($adminPassword)) {
                    throw ValidationException::withMessages([
                        'admin_password' => __('A password is required when creating a primary admin.'),
                    ]);
                }

                Staff::query()->create([
                    ...$attributes,
                    'email_verified_at' => now(),
                ]);

                return;
            }

            $staff->update($attributes);
        });
    }

    private function ensureEmailIsAvailable(string $email, ?int $ignoreUserId = null): void
    {
        $exists = User::query()
            ->where('email', $email)
            ->when($ignoreUserId !== null, fn ($query) => $query->whereKeyNot($ignoreUserId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'admin_email' => __('This email is already registered on the platform.'),
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [
            $parts[0] ?? 'Clinic',
            $parts[1] ?? 'Admin',
        ];
    }
}
