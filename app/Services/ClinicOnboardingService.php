<?php

namespace App\Services;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\BranchProfile;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ClinicServiceSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicOnboardingService
{
    /**
     * @param  array{
     *     name: string,
     *     type: string,
     *     email?: string|null,
     *     phone?: string|null,
     *     address?: string|null,
     *     logo?: UploadedFile|null,
     *     branch_name: string,
     *     branch_slug: string,
     *     domain: string,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     * }  $data
     */
    public function onboard(array $data): Organization
    {
        $organization = DB::transaction(function () use ($data): Organization {
            $organization = Organization::query()->create([
                'name' => $data['name'],
                'slug' => $this->uniqueOrganizationSlug($data['name']),
                'type' => OrganizationType::from($data['type']),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => true,
            ]);

            $admin = User::query()->create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'platform_role' => PlatformRole::OrgAdmin,
                'email_verified_at' => now(),
            ]);

            $organization->users()->attach($admin->id, [
                'role' => OrganizationRole::Owner->value,
            ]);

            return $organization;
        });

        $this->createBranch(
            organization: $organization,
            branchName: $data['branch_name'],
            branchSlug: $data['branch_slug'],
            domain: $data['domain'],
            contactEmail: $data['email'] ?? $data['admin_email'],
            adminEmail: $data['admin_email'],
            adminPassword: $data['admin_password'],
            adminName: $data['admin_name'],
            logo: $data['logo'] ?? null,
        );

        return $organization->loadCount('branches')->load('users');
    }

    /**
     * @param  array{
     *     branch_name: string,
     *     branch_slug: string,
     *     domain: string,
     *     contact_email?: string|null,
     *     admin_email?: string|null,
     *     admin_password?: string|null,
     *     admin_name?: string|null,
     * }  $data
     */
    public function addBranch(Organization $organization, array $data): Tenant
    {
        return $this->createBranch(
            organization: $organization,
            branchName: $data['branch_name'],
            branchSlug: $data['branch_slug'],
            domain: $data['domain'],
            contactEmail: $data['contact_email'] ?? $organization->email,
            adminEmail: $data['admin_email'] ?? null,
            adminPassword: $data['admin_password'] ?? null,
            adminName: $data['admin_name'] ?? null,
            provisionStaff: ($data['admin_email'] ?? null) !== null && ($data['admin_password'] ?? null) !== null,
        );
    }

    private function createBranch(
        Organization $organization,
        string $branchName,
        string $branchSlug,
        string $domain,
        ?string $contactEmail,
        ?string $adminEmail = null,
        ?string $adminPassword = null,
        ?string $adminName = null,
        bool $provisionStaff = true,
        ?UploadedFile $logo = null,
    ): Tenant {
        $tenant = Tenant::query()->create([
            'id' => $branchSlug,
            'organization_id' => $organization->id,
            'name' => $branchName,
            'slug' => $branchSlug,
            'is_active' => true,
        ]);

        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        $tenant->run(function () use ($branchName, $contactEmail, $adminEmail, $adminPassword, $adminName, $provisionStaff, $logo): void {
            $logoPath = $logo?->store('logos', 'public');

            BranchProfile::query()->create([
                'name' => $branchName,
                'contact_email' => $contactEmail,
                'logo_path' => $logoPath,
            ]);

            if (! $provisionStaff || $adminEmail === null || $adminPassword === null) {
                return;
            }

            [$firstName, $lastName] = $this->splitName($adminName ?? 'Branch Admin');

            Staff::query()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $adminEmail,
                'role' => StaffRole::ClinicAdmin,
                'password' => Hash::make($adminPassword),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            (new ClinicServiceSeeder)->run();
        });

        return $tenant->load('domains');
    }

    private function uniqueOrganizationSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 1;

        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [
            $parts[0] ?? 'Branch',
            $parts[1] ?? 'Admin',
        ];
    }
}
