<?php

namespace Tests\Feature;

use App\Enums\PlatformRole;
use App\Enums\RegistrationRequestStatus;
use App\Models\Organization;
use App\Models\OrganizationRegistrationRequest;
use App\Models\User;
use App\Support\BranchIdentifier;
use App\Support\TenantPortal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnboardingSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_clinic_auto_generates_slug_and_uses_path_access(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $clinicName = 'Harbor '.Str::lower(fake()->unique()->lexify('????')).' Dental';

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => $clinicName,
            'type' => 'single',
            'email' => 'contact@harbor.test',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@harbor.'.fake()->unique()->lexify('????').'.test',
            'admin_password' => 'password',
        ]);

        $organization = Organization::query()->where('name', $clinicName)->firstOrFail();
        $expectedBranchName = BranchIdentifier::suggestBranchName($clinicName);
        $expectedSlug = BranchIdentifier::slugFromName($expectedBranchName);

        $response->assertRedirect(route('clinics.show', $organization));

        $this->assertFalse($organization->fresh()->is_active);

        $tenant = $organization->branches()->firstOrFail();
        $this->assertSame($tenant->id, $organization->fresh()->admin_branch_id);

        $this->assertDatabaseHas('tenants', [
            'organization_id' => $organization->id,
            'name' => $expectedBranchName,
            'slug' => $expectedSlug,
            'is_active' => false,
        ]);

        $this->assertDatabaseMissing('domains', [
            'tenant_id' => $expectedSlug,
        ]);
    }

    public function test_add_branch_auto_generates_slug_without_domain(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'Harbor Dental',
            'email' => 'contact@harbor.test',
        ]);

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $branchName = 'Uptown '.Str::lower(fake()->unique()->lexify('????')).' Clinic';
        $expectedSlug = BranchIdentifier::slugFromName($branchName);

        $response = $this->actingAs($user)->post(route('clinics.branches.store', $organization), [
            'branch_name' => $branchName,
        ]);

        $response->assertRedirect(route('clinics.branches.index', $organization));

        $this->assertDatabaseHas('tenants', [
            'organization_id' => $organization->id,
            'name' => $branchName,
            'slug' => $expectedSlug,
        ]);

        $this->assertDatabaseMissing('domains', [
            'tenant_id' => $expectedSlug,
        ]);

        $tenant = $organization->branches()->where('slug', $expectedSlug)->firstOrFail();

        $this->assertSame(
            $organization->slug.'/'.$expectedSlug,
            TenantPortal::pathPrefix($tenant),
        );
    }

    public function test_onboarding_form_shows_suggested_branch_name_and_app_host_path(): void
    {
        $registrationRequest = OrganizationRegistrationRequest::factory()->approved()->create([
            'name' => 'Sunrise Dental',
            'status' => RegistrationRequestStatus::Approved,
        ]);

        $suggested = BranchIdentifier::suggestBranchName('Sunrise Dental');

        $response = $this->get(route('onboarding.show', $registrationRequest->onboarding_token));

        $response->assertOk();
        $response->assertSee($suggested, false);
        $response->assertSee(BranchIdentifier::baseHost(), false);
    }
}
