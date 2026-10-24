<?php

namespace App\Services;

use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\Tenant;

class PrimaryClinicAdminProvisioner
{
    /**
     * Ensure the clinic primary admin exists as ClinicAdmin staff on every branch.
     */
    public function syncToAllBranches(Organization $organization): void
    {
        $source = $this->primaryAdminSnapshot($organization);

        if ($source === null) {
            return;
        }

        foreach ($organization->branches()->get() as $branch) {
            $this->syncToBranch($branch, $source);
        }
    }

    /**
     * Copy primary admin credentials from the admin branch onto a target branch.
     */
    public function syncFromAdminBranch(Organization $organization, Tenant $target): void
    {
        $source = $this->primaryAdminSnapshot($organization);

        if ($source === null) {
            return;
        }

        $this->syncToBranch($target, $source);
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     password: string|null,
     * }|null
     */
    public function primaryAdminSnapshot(Organization $organization): ?array
    {
        $adminBranch = $organization->adminBranch;

        if (! $adminBranch instanceof Tenant) {
            return null;
        }

        return $adminBranch->run(function (): ?array {
            $staff = Staff::query()
                ->where('role', StaffRole::ClinicAdmin)
                ->orderBy('id')
                ->first();

            if ($staff === null) {
                return null;
            }

            return [
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'email' => $staff->email,
                // Already hashed — Laravel's `hashed` cast will not re-hash.
                'password' => $staff->getAttributes()['password'] ?? null,
            ];
        });
    }

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     password: string|null,
     * }  $source
     */
    public function syncToBranch(Tenant $branch, array $source): void
    {
        $branch->run(function () use ($source): void {
            $staff = Staff::query()
                ->where('email', $source['email'])
                ->first();

            if ($staff === null) {
                $staff = Staff::query()
                    ->where('role', StaffRole::ClinicAdmin)
                    ->orderBy('id')
                    ->first();
            }

            $attributes = [
                'first_name' => $source['first_name'],
                'last_name' => $source['last_name'],
                'email' => $source['email'],
                'role' => StaffRole::ClinicAdmin,
                'is_active' => true,
            ];

            if ($staff === null) {
                if (! filled($source['password'])) {
                    return;
                }

                Staff::query()->create([
                    ...$attributes,
                    'password' => $source['password'],
                    'email_verified_at' => now(),
                ]);

                return;
            }

            if (filled($source['password'])) {
                $attributes['password'] = $source['password'];
            }

            $staff->update($attributes);
        });
    }
}
