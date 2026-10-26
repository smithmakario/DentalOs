<?php

namespace Tests\Feature\Tenant;

use App\Enums\BillingCycle;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPathNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_clinic_path_shows_friendly_not_found_page(): void
    {
        $response = $this->get('http://127.0.0.1/missing-clinic/missing-branch/staff/login');

        $response->assertNotFound();
        $response->assertSee(__('Branch unavailable'));
        $response->assertSee(__('This clinic could not be found.'));
        $response->assertSee(__('Clinic login'));
        $response->assertDontSee('TenantCouldNotBeIdentifiedByPathException');
    }

    public function test_inactive_clinic_path_explains_activation_requirement(): void
    {
        $organization = Organization::factory()->create([
            'slug' => 'inactive-clinic',
            'is_active' => false,
        ]);

        Tenant::withoutEvents(fn (): Tenant => Tenant::query()->create([
            'id' => 'inactive-clinic-main',
            'organization_id' => $organization->id,
            'name' => 'Main',
            'slug' => 'inactive-clinic-main',
            'is_active' => false,
        ]));

        $response = $this->get('http://127.0.0.1/inactive-clinic/inactive-clinic-main/staff/login');

        $response->assertNotFound();
        $response->assertSee(__('This clinic is not active yet. A subscription plan must be assigned before staff can sign in.'));
    }

    public function test_active_clinic_with_missing_branch_shows_branch_not_found(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $organization = Organization::factory()->create([
            'slug' => 'active-clinic',
            'type' => OrganizationType::Single,
            'is_active' => false,
        ]);

        Tenant::withoutEvents(fn (): Tenant => Tenant::query()->create([
            'id' => 'active-clinic-main',
            'organization_id' => $organization->id,
            'name' => 'Main',
            'slug' => 'active-clinic-main',
            'is_active' => false,
        ]));

        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => OrganizationType::Single,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('clinics.subscription.assign', $organization), [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
        ])->assertRedirect();

        $response = $this->get('http://127.0.0.1/active-clinic/does-not-exist/staff/login');

        $response->assertNotFound();
        $response->assertSee(__('This branch could not be found.'));
    }
}
