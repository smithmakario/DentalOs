<?php

namespace Tests;

use App\Enums\BillingCycle;
use App\Enums\OrganizationSubscriptionStatus;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Staff;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Organization $organization;

    protected string $tenantDomain;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(8));
        $tenantId = 'branch-'.$suffix;
        $this->tenantDomain = $tenantId.'.localhost';

        $this->organization = Organization::factory()->create([
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => $this->organization->type,
            'is_active' => true,
            'max_branches' => 10,
        ]);

        OrganizationSubscription::query()->create([
            'organization_id' => $this->organization->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly,
            'status' => OrganizationSubscriptionStatus::Active,
            'current_period_start' => now()->subDay(),
            'current_period_end' => now()->addMonth(),
        ]);

        // Single-DB tenancy: only create the tenant row (+ domain). Schema is central.
        $this->tenant = Tenant::query()->create([
            'id' => $tenantId,
            'organization_id' => $this->organization->id,
            'name' => 'Test Branch',
            'slug' => $tenantId,
            'is_active' => true,
        ]);

        $this->tenant->domains()->create(['domain' => $this->tenantDomain]);
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    protected function tenantUrl(string $path = '/'): string
    {
        return 'http://'.$this->tenantDomain.$path;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createStaff(array $attributes = []): Staff
    {
        return $this->tenant->run(fn (): Staff => Staff::factory()->create($attributes));
    }
}
