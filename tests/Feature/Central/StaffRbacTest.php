<?php

namespace Tests\Feature\Central;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Enums\StaffPermission;
use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\StaffMember;
use App\Models\User;
use App\Services\StaffAccessService;
use App\Support\StaffRolePermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantBranches;
use Tests\TestCase;

class StaffRbacTest extends TestCase
{
    use CreatesTenantBranches;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->tearDownProvisionedTenants();

        parent::tearDown();
    }

    public function test_dentist_role_includes_view_patient_charts_permission(): void
    {
        $this->assertTrue(
            StaffRolePermissions::roleHas(StaffRole::Dentist, StaffPermission::ViewPatientCharts)
        );
    }

    public function test_hygienist_cannot_manage_patients(): void
    {
        $this->assertFalse(
            StaffRolePermissions::roleHas(StaffRole::Hygienist, StaffPermission::ManagePatients)
        );
    }

    public function test_staff_member_with_single_branch_assignment_can_only_access_that_branch(): void
    {
        $organization = Organization::factory()->create();
        $branchA = $this->createTenantBranch($organization, 'branch-a', 'Branch A');
        $branchB = $this->createTenantBranch($organization, 'branch-b', 'Branch B');

        $staffMember = StaffMember::factory()->create([
            'organization_id' => $organization->id,
            'has_global_branch_access' => false,
        ]);

        $staffMember->branchAssignments()->create(['tenant_id' => $branchA->id]);

        $service = app(StaffAccessService::class);

        $this->assertTrue($service->canAccessTenant($staffMember, $branchA->id));
        $this->assertFalse($service->canAccessTenant($staffMember, $branchB->id));
    }

    public function test_global_access_staff_can_access_all_organization_branches(): void
    {
        $organization = Organization::factory()->create();
        $this->createTenantBranch($organization, 'branch-a', 'Branch A');
        $this->createTenantBranch($organization, 'branch-b', 'Branch B');

        $staffMember = StaffMember::factory()->globalAccess()->create([
            'organization_id' => $organization->id,
        ]);

        $service = app(StaffAccessService::class);

        $this->assertCount(2, $service->accessibleBranches($staffMember));
    }

    public function test_super_admin_can_create_staff_with_branch_assignments(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();
        $this->createTenantBranch($organization, 'branch-a', 'Branch A');

        $response = $this->actingAs($user)->post(route('clinics.staff.store', $organization), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.test',
            'password' => 'password',
            'role' => StaffRole::Dentist->value,
            'has_global_branch_access' => false,
            'branch_ids' => ['branch-a'],
            'is_active' => true,
        ]);

        $response->assertRedirect(route('clinics.staff.index', $organization));
        $this->assertDatabaseHas('staff_members', [
            'email' => 'jane.doe@example.test',
            'organization_id' => $organization->id,
        ]);
        $this->assertDatabaseHas('staff_branch_assignments', [
            'tenant_id' => 'branch-a',
        ]);
    }

    public function test_super_admin_sees_primary_admin_in_clinic_staff_list(): void
    {
        $superAdmin = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $suffix = fake()->unique()->lexify('????');

        $this->actingAs($superAdmin)->post(route('clinics.store'), [
            'name' => 'Staff List Clinic '.$suffix,
            'type' => 'single',
            'email' => 'contact@staff-list-'.$suffix.'.test',
            'admin_name' => 'Primary Admin',
            'admin_email' => 'primary@staff-list-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()
            ->where('email', 'contact@staff-list-'.$suffix.'.test')
            ->firstOrFail();

        $this->assertDatabaseHas('staff_members', [
            'organization_id' => $organization->id,
            'email' => 'primary@staff-list-'.$suffix.'.test',
            'role' => StaffRole::ClinicAdmin->value,
            'has_global_branch_access' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('clinics.staff.index', $organization))
            ->assertOk()
            ->assertSee('Primary Admin')
            ->assertSee('primary@staff-list-'.$suffix.'.test')
            ->assertDontSee(__('No staff members yet.'));
    }

    public function test_org_admin_cannot_access_platform_staff_routes(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::OrgAdmin]);
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $otherOrganization = Organization::factory()->create();

        $this->actingAs($user)->get(route('clinics.staff.index', $organization))
            ->assertRedirect(route('clinic.login'));

        $this->actingAs($user)->get(route('clinics.staff.index', $otherOrganization))
            ->assertRedirect(route('clinic.login'));
    }

    public function test_super_admin_can_update_staff_member(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();
        $this->createTenantBranch($organization, 'branch-a', 'Branch A');

        $staffMember = StaffMember::factory()->create([
            'organization_id' => $organization->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'role' => StaffRole::Receptionist,
            'has_global_branch_access' => true,
        ]);

        $response = $this->actingAs($user)->patch(
            route('clinics.staff.update', [$organization, $staffMember]),
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => $staffMember->email,
                'role' => StaffRole::Dentist->value,
                'has_global_branch_access' => false,
                'branch_ids' => ['branch-a'],
                'is_active' => true,
            ],
        );

        $response->assertRedirect(route('clinics.staff.index', $organization));
        $this->assertDatabaseHas('staff_members', [
            'id' => $staffMember->id,
            'last_name' => 'Smith',
            'role' => StaffRole::Dentist->value,
            'has_global_branch_access' => false,
        ]);
        $this->assertDatabaseHas('staff_branch_assignments', [
            'staff_member_id' => $staffMember->id,
            'tenant_id' => 'branch-a',
        ]);
    }

    public function test_creating_staff_without_branches_or_global_access_fails_validation(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();

        $response = $this->actingAs($user)->post(route('clinics.staff.store', $organization), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.test',
            'password' => 'password',
            'role' => StaffRole::Dentist->value,
            'has_global_branch_access' => false,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('branch_ids');
    }
}
