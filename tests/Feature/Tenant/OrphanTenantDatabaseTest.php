<?php

namespace Tests\Feature\Tenant;

use App\Enums\PlatformRole;
use App\Models\BranchProfile;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ClinicOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrphanTenantDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_branch_does_not_require_create_database_privilege(): void
    {
        $branchSlug = 'orphan-branch-'.fake()->unique()->lexify('????');

        $organization = Organization::factory()->create([
            'is_active' => false,
        ]);

        $tenant = app(ClinicOnboardingService::class)->addBranch($organization, [
            'branch_name' => 'Orphan Branch',
            'branch_slug' => $branchSlug,
            'contact_email' => 'contact@orphan.test',
        ]);

        $this->assertSame($branchSlug, $tenant->id);
        $this->assertDatabaseHas('tenants', [
            'id' => $branchSlug,
            'organization_id' => $organization->id,
        ]);

        $tenant->run(function () use ($branchSlug): void {
            $this->assertDatabaseHas('branch_profiles', [
                'tenant_id' => $branchSlug,
                'name' => 'Orphan Branch',
            ]);
        });
    }

    public function test_super_admin_can_create_clinic_in_single_database(): void
    {
        $suffix = fake()->unique()->lexify('????');
        $branchSlug = 'healing-stream-'.$suffix;

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Healing Stream '.$suffix,
            'type' => 'single',
            'email' => 'contact@healing-'.$suffix.'.test',
            'branch_name' => 'Healing Stream '.$suffix.' - Main',
            'branch_slug' => $branchSlug,
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@healing-'.$suffix.'.test',
            'admin_password' => 'password',
        ]);

        $organization = Organization::query()->where('email', 'contact@healing-'.$suffix.'.test')->firstOrFail();

        $response->assertRedirect(route('clinics.show', $organization));
        $this->assertDatabaseHas('tenants', [
            'id' => $branchSlug,
            'organization_id' => $organization->id,
        ]);

        $tenant = Tenant::query()->findOrFail($branchSlug);
        $adminEmail = 'admin@healing-'.$suffix.'.test';

        $tenant->run(function () use ($branchSlug, $adminEmail): void {
            $this->assertSame(1, BranchProfile::query()->where('tenant_id', $branchSlug)->count());
            $this->assertSame(1, Staff::query()->where('tenant_id', $branchSlug)->count());
            $this->assertDatabaseHas('tenant_users', [
                'tenant_id' => $branchSlug,
                'email' => $adminEmail,
            ]);
        });
    }
}
