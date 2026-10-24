<?php

namespace Tests\Feature\Central;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesTenantBranches;
use Tests\TestCase;

class ClinicPrimaryAdminTest extends TestCase
{
    use CreatesTenantBranches;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->tearDownProvisionedTenants();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    public function test_super_admin_can_update_clinic_primary_admin(): void
    {
        $organization = Organization::factory()->create();
        $tenant = $this->createTenantBranch(
            $organization,
            'admin-'.Str::lower(fake()->unique()->lexify('????')),
            'Admin Branch',
        );
        $organization->update(['admin_branch_id' => $tenant->id]);

        $admin = User::factory()->create([
            'name' => 'Old Admin',
            'email' => 'old-admin@clinic.test',
            'password' => 'password',
            'platform_role' => PlatformRole::OrgAdmin,
        ]);
        $organization->users()->attach($admin->id, ['role' => OrganizationRole::Owner->value]);

        $tenant->run(function (): void {
            Staff::factory()->create([
                'first_name' => 'Old',
                'last_name' => 'Admin',
                'email' => 'old-admin@clinic.test',
                'password' => 'password',
                'role' => StaffRole::ClinicAdmin,
            ]);
        });

        $superAdmin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($superAdmin)->patch(route('clinics.primary-admin.update', $organization), [
            'admin_name' => 'New Admin',
            'admin_email' => 'new-admin@clinic.test',
            'admin_password' => 'new-password',
            'admin_password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('clinics.show', $organization));

        $admin->refresh();
        $this->assertSame('New Admin', $admin->name);
        $this->assertSame('new-admin@clinic.test', $admin->email);
        $this->assertTrue(Hash::check('new-password', $admin->password));

        $tenant->run(function (): void {
            $staff = Staff::query()->where('role', StaffRole::ClinicAdmin)->first();

            $this->assertNotNull($staff);
            $this->assertSame('New', $staff->first_name);
            $this->assertSame('Admin', $staff->last_name);
            $this->assertSame('new-admin@clinic.test', $staff->email);
            $this->assertTrue(Hash::check('new-password', $staff->password));
        });
    }

    public function test_super_admin_can_update_primary_admin_via_admin_branch_route(): void
    {
        $organization = Organization::factory()->create();
        $adminBranch = $this->createTenantBranch(
            $organization,
            'hq-'.Str::lower(fake()->unique()->lexify('????')),
            'HQ',
        );
        $organization->update(['admin_branch_id' => $adminBranch->id]);

        $admin = User::factory()->create([
            'name' => 'Old Admin',
            'email' => 'old-hq@clinic.test',
            'password' => 'password',
            'platform_role' => PlatformRole::OrgAdmin,
        ]);
        $organization->users()->attach($admin->id, ['role' => OrganizationRole::Owner->value]);

        $adminBranch->run(function (): void {
            Staff::factory()->create([
                'first_name' => 'Old',
                'last_name' => 'Admin',
                'email' => 'old-hq@clinic.test',
                'password' => 'password',
                'role' => StaffRole::ClinicAdmin,
            ]);
        });

        $superAdmin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($superAdmin)->patch(
            route('clinics.branches.primary-admin.update', [$organization, $adminBranch]),
            [
                'admin_name' => 'HQ Lead',
                'admin_email' => 'lead@hq.test',
                'admin_password' => 'branch-password',
                'admin_password_confirmation' => 'branch-password',
            ],
        );

        $response->assertRedirect(route('clinics.branches.index', $organization));

        $adminBranch->run(function (): void {
            $staff = Staff::query()->where('email', 'lead@hq.test')->first();

            $this->assertNotNull($staff);
            $this->assertSame(StaffRole::ClinicAdmin, $staff->role);
            $this->assertTrue(Hash::check('branch-password', $staff->password));
        });
    }

    public function test_super_admin_cannot_create_clinic_admin_on_satellite_branch(): void
    {
        $organization = Organization::factory()->create();
        $adminBranch = $this->createTenantBranch(
            $organization,
            'hq-'.Str::lower(fake()->unique()->lexify('????')),
            'HQ',
        );
        $satellite = $this->createTenantBranch(
            $organization,
            'sat-'.Str::lower(fake()->unique()->lexify('????')),
            'Satellite',
        );
        $organization->update(['admin_branch_id' => $adminBranch->id]);

        $superAdmin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($superAdmin)->patch(
            route('clinics.branches.primary-admin.update', [$organization, $satellite]),
            [
                'admin_name' => 'Branch Lead',
                'admin_email' => 'lead@satellite.test',
                'admin_password' => 'branch-password',
                'admin_password_confirmation' => 'branch-password',
            ],
        );

        $response->assertSessionHasErrors('admin_email');

        $satellite->run(function (): void {
            $this->assertFalse(
                Staff::query()->where('email', 'lead@satellite.test')->exists(),
            );
        });
    }

    public function test_non_super_admin_cannot_update_clinic_primary_admin(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'platform_role' => PlatformRole::OrgAdmin,
        ]);
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->patch(route('clinics.primary-admin.update', $organization), [
            'admin_name' => 'Hacker',
            'admin_email' => 'hacker@clinic.test',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ])->assertRedirect(route('clinic.login'));
    }
}
