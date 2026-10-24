<?php

namespace Tests\Feature\Central;

use App\Enums\BillingCycle;
use App\Enums\OrganizationSubscriptionStatus;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ClinicSubscriptionAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicSubscriptionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_clinic_is_inactive_until_plan_is_assigned(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Pending Activation Clinic',
            'type' => OrganizationType::Single->value,
            'email' => 'contact@pending-activation.test',
            'branch_name' => 'Pending Activation Clinic - Main',
            'branch_slug' => 'pending-activation-main',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@pending-activation.test',
            'admin_password' => 'password',
        ]);

        $organization = Organization::query()->where('name', 'Pending Activation Clinic')->firstOrFail();

        $response->assertRedirect(route('clinics.show', $organization));

        $this->assertFalse($organization->fresh()->is_active);
        $this->assertFalse((bool) $organization->branches()->first()?->is_active);
    }

    public function test_assigning_plan_activates_clinic_and_branches(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $organization = Organization::factory()->create([
            'type' => OrganizationType::Single,
            'is_active' => false,
        ]);

        $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::query()->create([
            'id' => 'inactive-branch',
            'organization_id' => $organization->id,
            'name' => 'Inactive Branch',
            'slug' => 'inactive-branch',
            'is_active' => false,
        ]));

        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => OrganizationType::Single,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.subscription.assign', $organization), [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
        ]);

        $response->assertRedirect(route('clinics.show', $organization));

        $this->assertTrue($organization->fresh()->is_active);
        $this->assertTrue($tenant->fresh()->is_active);
        $this->assertDatabaseHas('organization_subscriptions', [
            'organization_id' => $organization->id,
            'subscription_plan_id' => $plan->id,
            'status' => OrganizationSubscriptionStatus::Active->value,
        ]);
    }

    public function test_staff_access_allowed_within_grace_period_after_due_date(): void
    {
        $organization = Organization::factory()->create(['is_active' => true]);
        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => $organization->type,
        ]);

        OrganizationSubscription::query()->create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly,
            'status' => OrganizationSubscriptionStatus::Active,
            'current_period_start' => now()->subMonths(2),
            'current_period_end' => now()->subDays(3),
        ]);

        $this->assertTrue(app(ClinicSubscriptionAccess::class)->allowsStaffAccess($organization->fresh()));
    }

    public function test_staff_access_blocked_after_grace_period(): void
    {
        $organization = Organization::factory()->create(['is_active' => true]);
        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => $organization->type,
        ]);

        OrganizationSubscription::query()->create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly,
            'status' => OrganizationSubscriptionStatus::Active,
            'current_period_start' => now()->subMonths(2),
            'current_period_end' => now()->subDays(6),
        ]);

        $this->assertFalse(app(ClinicSubscriptionAccess::class)->allowsStaffAccess($organization->fresh()));
    }

    public function test_clinic_show_page_is_available_without_edit(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $organization = Organization::factory()->create([
            'name' => 'Viewable Clinic',
        ]);

        $response = $this->actingAs($user)->get(route('clinics.show', $organization));

        $response->assertOk();
        $response->assertSee('Viewable Clinic');
        $response->assertSee(__('Manage Staff'));
        $response->assertSee(__('Manage Branches'));
    }

    public function test_subscription_plan_slug_is_auto_generated_from_name(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('subscription-plans.store'), [
            'name' => 'Growth Plus Plan',
            'organization_type' => OrganizationType::Single->value,
            'price_monthly' => 10000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('subscription-plans.index'));
        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Growth Plus Plan',
            'slug' => 'growth-plus-plan',
        ]);

        $plan = SubscriptionPlan::query()->where('slug', 'growth-plus-plan')->firstOrFail();
        $this->assertEquals(120000.0, (float) $plan->price_yearly);
    }
}
