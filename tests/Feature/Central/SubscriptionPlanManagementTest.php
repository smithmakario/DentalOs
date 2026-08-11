<?php

namespace Tests\Feature\Central;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_subscription_plans_index(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        SubscriptionPlan::factory()->create([
            'name' => 'Starter',
            'organization_type' => OrganizationType::Single,
        ]);

        $response = $this->actingAs($user)->get(route('subscription-plans.index'));

        $response->assertOk();
        $response->assertSee(__('Subscription Plans'));
        $response->assertSee('Starter');
        $response->assertSee(__('Professional (Single Practice)'));
    }

    public function test_org_admin_cannot_manage_subscription_plans(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::OrgAdmin]);
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->get(route('subscription-plans.index'))->assertForbidden();
        $this->actingAs($user)->get(route('subscription-plans.create'))->assertForbidden();
    }

    public function test_super_admin_can_create_professional_subscription_plan(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->post(route('subscription-plans.store'), [
            'name' => 'Growth',
            'slug' => 'growth',
            'organization_type' => OrganizationType::Single->value,
            'description' => 'For growing single-practice clinics.',
            'price_monthly' => 49900,
            'price_yearly' => 499000,
            'max_branches' => 3,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('subscription-plans.index'));
        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Growth',
            'slug' => 'growth',
            'organization_type' => OrganizationType::Single->value,
            'max_branches' => 3,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_create_enterprise_subscription_plan(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->post(route('subscription-plans.store'), [
            'name' => 'Enterprise Plus',
            'slug' => 'enterprise-plus',
            'organization_type' => OrganizationType::Dso->value,
            'description' => 'For large multi-location networks.',
            'price_monthly' => 299900,
            'price_yearly' => 2999000,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('subscription-plans.index'));
        $this->assertDatabaseHas('subscription_plans', [
            'slug' => 'enterprise-plus',
            'organization_type' => OrganizationType::Dso->value,
        ]);
    }

    public function test_super_admin_can_update_subscription_plan(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Legacy',
            'organization_type' => OrganizationType::Single,
        ]);

        $response = $this->actingAs($user)->patch(route('subscription-plans.update', $plan), [
            'name' => 'Legacy Plus',
            'slug' => $plan->slug,
            'organization_type' => OrganizationType::Dso->value,
            'description' => 'Updated description',
            'price_monthly' => 150000,
            'price_yearly' => 1500000,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('subscription-plans.index'));
        $plan->refresh();
        $this->assertSame('Legacy Plus', $plan->name);
        $this->assertSame(OrganizationType::Dso, $plan->organization_type);
        $this->assertFalse($plan->is_active);
    }

    public function test_checkout_only_shows_plans_matching_organization_type(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create(['type' => OrganizationType::Single]);

        $singlePlan = SubscriptionPlan::factory()->create([
            'name' => 'Single Plan',
            'organization_type' => OrganizationType::Single,
        ]);
        SubscriptionPlan::factory()->create([
            'name' => 'Enterprise Only Plan',
            'organization_type' => OrganizationType::Dso,
        ]);

        $response = $this->actingAs($user)->get(route('subscriptions.checkout', $organization));

        $response->assertOk();
        $response->assertSee('Single Plan');
        $response->assertDontSee('Enterprise Only Plan');
    }
}
