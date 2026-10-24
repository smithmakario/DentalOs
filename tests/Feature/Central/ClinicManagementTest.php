<?php

namespace Tests\Feature\Central;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Http\Controllers\Tenant\MediaController;
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

    public function test_org_admin_cannot_access_platform_clinic_routes(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::OrgAdmin,
        ]);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->get(route('clinics.create'))
            ->assertRedirect(route('clinic.login'));

        $this->actingAs($user)->get(route('clinics.edit', $organization))
            ->assertRedirect(route('clinic.login'));
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
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@bright.test',
            'admin_password' => 'password',
        ]);

        $response->assertRedirect(route('clinics.show', $organization));
        $response->assertSessionHas('status');
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
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@bright.test',
            'admin_password' => 'password',
            'logo' => UploadedFile::fake()->image('logo.png', 512, 512),
        ]);

        $organization = Organization::query()->where('name', 'Bright Dental')->firstOrFail();
        $tenant = $organization->branches()->firstOrFail();

        $response->assertRedirect(route('clinics.show', $organization));

        $tenant->run(function (): void {
            $profile = BranchProfile::query()->firstOrFail();

            $this->assertNotNull($profile->logo_path);
            Storage::disk('public')->assertExists($profile->logo_path);
        });

        $logoUrl = $tenant->run(fn (): ?string => BranchProfile::query()->first()?->logoUrl());

        $this->assertNotNull($logoUrl);
        $this->assertStringContainsString('/'.$organization->slug.'/'.$tenant->slug.'/media/', $logoUrl);

        // Storage::fake + filesystem tenancy do not share the same disk during HTTP;
        // assert the media controller can stream the logo in tenant context instead.
        $tenant->run(function () use ($logoUrl): void {
            $path = BranchProfile::query()->value('logo_path');
            $response = app(MediaController::class)(request(), $path);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringEndsWith('/media/'.$path, parse_url($logoUrl, PHP_URL_PATH) ?? '');
        });

        $this->actingAs($user)
            ->get(route('clinics.show', $organization))
            ->assertOk()
            ->assertSee($logoUrl, false);
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
        ]);

        $response->assertRedirect(route('clinics.branches.index', $organization));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('tenants', [
            'id' => $branchSlug,
            'organization_id' => $organization->id,
            'name' => 'Uptown Branch',
            'slug' => $branchSlug,
        ]);

        $this->assertDatabaseMissing('domains', [
            'tenant_id' => $branchSlug,
        ]);
    }

    public function test_clinics_index_can_filter_by_status(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $active = Organization::factory()->create(['name' => 'Active Clinic', 'is_active' => true]);
        $inactive = Organization::factory()->create(['name' => 'Inactive Clinic', 'is_active' => false]);

        $response = $this->actingAs($user)->get(route('clinics.index', ['status' => 'active']));

        $response->assertOk();
        $response->assertSee('Active Clinic');
        $response->assertSee('ID: DF-'.str_pad((string) $active->id, 5, '0', STR_PAD_LEFT));
        // Inactive clinics still appear in the notification feed; assert against the table row id.
        $response->assertDontSee('ID: DF-'.str_pad((string) $inactive->id, 5, '0', STR_PAD_LEFT));
    }
}
