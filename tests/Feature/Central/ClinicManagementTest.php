<?php

namespace Tests\Feature\Central;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Models\BranchProfile;
use App\Models\Organization;
use App\Models\User;
use App\Services\ClinicOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClinicManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_clinics_index(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->get(route('clinics.index'));

        $response->assertOk();
        $response->assertSee(__('Clinic Management'));
    }

    public function test_super_admin_can_view_create_clinic_form(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->get(route('clinics.create'));

        $response->assertOk();
        $response->assertSee(__('Onboard New Clinic'));
    }

    public function test_org_admin_cannot_view_create_clinic_form(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::OrgAdmin,
        ]);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('clinics.create'));

        $response->assertForbidden();
    }

    public function test_org_admin_can_edit_their_clinic(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::OrgAdmin,
        ]);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('clinics.edit', $organization));

        $response->assertOk();
        $response->assertSee(__('Edit Clinic'));
    }

    public function test_org_admin_cannot_edit_another_clinic(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::OrgAdmin,
        ]);

        $ownedOrganization = Organization::factory()->create();
        $ownedOrganization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $otherOrganization = Organization::factory()->create();

        $response = $this->actingAs($user)->get(route('clinics.edit', $otherOrganization));

        $response->assertForbidden();
    }

    public function test_store_clinic_requires_valid_payload(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), []);

        $response->assertSessionHasErrors([
            'name',
            'type',
            'branch_name',
            'branch_slug',
            'domain',
            'admin_name',
            'admin_email',
            'admin_password',
        ]);
    }

    public function test_super_admin_can_store_clinic(): void
    {
        $organization = Organization::factory()->create();

        $this->mock(ClinicOnboardingService::class, function ($mock) use ($organization): void {
            $mock->shouldReceive('onboard')
                ->once()
                ->andReturn($organization);
        });

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Bright Dental',
            'type' => 'single',
            'email' => 'contact@bright.test',
            'branch_name' => 'Main Branch',
            'branch_slug' => 'bright-main',
            'domain' => 'bright.localhost',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@bright.test',
            'admin_password' => 'password',
        ]);

        $response->assertRedirect(route('clinics.edit', $organization));
        $response->assertSessionHas('status');
    }

    public function test_store_clinic_normalizes_domain_before_persisting(): void
    {
        Storage::fake('public');

        $branchSlug = 'domain-'.fake()->unique()->lexify('????');

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Domain Normalize Dental',
            'type' => 'single',
            'email' => 'contact@domain.test',
            'branch_name' => 'Main Branch',
            'branch_slug' => $branchSlug,
            'domain' => 'http://'.$branchSlug.'.localhost:8000',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@domain.test',
            'admin_password' => 'password',
        ]);

        $organization = Organization::query()->where('name', 'Domain Normalize Dental')->firstOrFail();

        $response->assertRedirect(route('clinics.edit', $organization));

        $this->assertDatabaseHas('domains', [
            'domain' => $branchSlug.'.localhost',
            'tenant_id' => $branchSlug,
        ]);
    }

    public function test_store_clinic_rejects_invalid_logo(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Bright Dental',
            'type' => 'single',
            'email' => 'contact@bright.test',
            'branch_name' => 'Main Branch',
            'branch_slug' => 'bright-main',
            'domain' => 'bright.localhost',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@bright.test',
            'admin_password' => 'password',
            'logo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('logo');
    }

    public function test_onboarding_stores_logo_on_branch_profile(): void
    {
        Storage::fake('public');

        $branchSlug = 'bright-'.fake()->unique()->lexify('????');

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Bright Dental',
            'type' => 'single',
            'email' => 'contact@bright.test',
            'branch_name' => 'Main Branch',
            'branch_slug' => $branchSlug,
            'domain' => $branchSlug.'.localhost',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@bright.test',
            'admin_password' => 'password',
            'logo' => UploadedFile::fake()->image('logo.png', 512, 512),
        ]);

        $organization = Organization::query()->where('name', 'Bright Dental')->firstOrFail();
        $tenant = $organization->branches()->firstOrFail();

        $response->assertRedirect(route('clinics.edit', $organization));

        $tenant->run(function (): void {
            $profile = BranchProfile::query()->firstOrFail();

            $this->assertNotNull($profile->logo_path);
            Storage::disk('public')->assertExists($profile->logo_path);
        });
    }

    public function test_super_admin_can_add_branch_without_admin_credentials(): void
    {
        $organization = Organization::factory()->create([
            'email' => 'contact@clinic.test',
        ]);

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $branchSlug = 'uptown-'.fake()->unique()->lexify('????');

        $response = $this->actingAs($user)->post(route('clinics.branches.store', $organization), [
            'branch_name' => 'Uptown Branch',
            'branch_slug' => $branchSlug,
            'domain' => $branchSlug.'.localhost',
        ]);

        $response->assertRedirect(route('clinics.branches.index', $organization));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('tenants', [
            'id' => $branchSlug,
            'organization_id' => $organization->id,
            'name' => 'Uptown Branch',
            'slug' => $branchSlug,
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => $branchSlug.'.localhost',
            'tenant_id' => $branchSlug,
        ]);
    }

    public function test_clinics_index_can_filter_by_status(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        Organization::factory()->create(['name' => 'Active Clinic', 'is_active' => true]);
        Organization::factory()->create(['name' => 'Inactive Clinic', 'is_active' => false]);

        $response = $this->actingAs($user)->get(route('clinics.index', ['status' => 'active']));

        $response->assertOk();
        $response->assertSee('Active Clinic');
        $response->assertDontSee('Inactive Clinic');
    }
}
