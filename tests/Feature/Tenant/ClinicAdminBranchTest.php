<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Tests\TenantTestCase;

class ClinicAdminBranchTest extends TenantTestCase
{
    public function test_clinic_admin_on_admin_branch_can_manage_clinic_branches(): void
    {
        $this->organization->update([
            'admin_branch_id' => $this->tenant->id,
        ]);

        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'clinic-admin@branch.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin, 'staff')
            ->get($this->tenantUrl('/clinic-branches'));

        $response->assertOk();
        $response->assertSee(__('Clinic Branches'));
        $response->assertSee(__('Add Branch'));
    }

    public function test_clinic_admin_on_non_admin_branch_cannot_manage_clinic_branches(): void
    {
        $otherAdminBranch = Tenant::withoutEvents(fn (): Tenant => Tenant::query()->create([
            'id' => 'other-admin-'.fake()->unique()->lexify('????'),
            'organization_id' => $this->organization->id,
            'name' => 'Other Admin Branch',
            'slug' => 'other-admin-'.fake()->unique()->lexify('????'),
            'is_active' => true,
        ]));

        $this->organization->update([
            'admin_branch_id' => $otherAdminBranch->id,
        ]);

        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'branch-admin@branch.test',
        ]);

        $this->actingAs($admin, 'staff')
            ->get($this->tenantUrl('/clinic-branches'))
            ->assertForbidden();
    }

    public function test_clinic_admin_on_admin_branch_can_create_branch(): void
    {
        $this->organization->update([
            'admin_branch_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'creator@branch.test',
        ]);

        $branchSlug = 'uptown-'.fake()->unique()->lexify('????');

        $response = $this->actingAs($admin, 'staff')
            ->post($this->tenantUrl('/clinic-branches'), [
                'branch_name' => 'Uptown Clinic',
                'branch_slug' => $branchSlug,
            ]);

        $response->assertRedirect($this->tenantUrl('/clinic-branches'));
        $response->assertSessionHas('status');
        $response->assertSessionDoesntHaveErrors();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $this->assertDatabaseHas('tenants', [
            'organization_id' => $this->organization->id,
            'slug' => $branchSlug,
            'name' => 'Uptown Clinic',
        ]);

        $branch = Tenant::query()->where('slug', $branchSlug)->firstOrFail();

        try {
            $branch->run(function () use ($admin): void {
                $this->assertTrue(
                    Staff::query()
                        ->where('email', $admin->email)
                        ->where('role', StaffRole::ClinicAdmin)
                        ->exists(),
                );
            });
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }

            try {
                $branch->database()->manager()->deleteDatabase($branch);
            } catch (\Throwable) {
                //
            }

            Tenant::withoutEvents(function () use ($branch): void {
                $branch->domains()->delete();
                $branch->delete();
            });
        }
    }

    public function test_dentist_cannot_access_clinic_branches(): void
    {
        $this->organization->update([
            'admin_branch_id' => $this->tenant->id,
        ]);

        $dentist = $this->createStaff([
            'role' => StaffRole::Dentist,
            'email' => 'dentist@branch.test',
        ]);

        $this->actingAs($dentist, 'staff')
            ->get($this->tenantUrl('/clinic-branches'))
            ->assertForbidden();
    }
}
