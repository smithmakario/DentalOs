<?php

namespace Tests\Feature\Tenant;

use App\Enums\BillingCycle;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ClinicSubscriptionAccess;
use App\Support\TenantPortal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PathTenantNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    public function test_path_tenant_dashboard_nav_links_include_org_and_branch_prefix(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $suffix = Str::lower(fake()->unique()->lexify('????'));

        $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Nav Clinic '.$suffix,
            'type' => 'single',
            'email' => 'contact@nav-'.$suffix.'.test',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@nav-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()->where('email', 'contact@nav-'.$suffix.'.test')->firstOrFail();
        $tenant = $organization->branches()->firstOrFail();
        $organization->update(['admin_branch_id' => $tenant->id]);

        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => $organization->type,
            'is_active' => true,
        ]);

        app(ClinicSubscriptionAccess::class)->assignPlan(
            $organization,
            $plan,
            BillingCycle::Monthly,
        );

        $admin = $tenant->run(fn (): Staff => Staff::query()
            ->where('email', 'admin@nav-'.$suffix.'.test')
            ->firstOrFail());

        $this->assertSame(StaffRole::ClinicAdmin, $admin->role);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $prefix = '/'.$organization->slug.'/'.$tenant->slug;

        $dashboard = $this->actingAs($admin, 'staff')
            ->get(TenantPortal::url($tenant, '/dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee($prefix.'/patients', false);
        $dashboard->assertSee($prefix.'/staff', false);
        $dashboard->assertSee($prefix.'/appointments', false);

        $this->actingAs($admin, 'staff')
            ->get(TenantPortal::url($tenant, '/patients'))
            ->assertOk()
            ->assertSee(__('Patient Management'));

        $login = $this->actingAs($admin, 'staff')
            ->get(TenantPortal::loginUrl($tenant));

        $login->assertOk();
        $login->assertSee(__('Continue to dashboard'));
        $login->assertSee($prefix.'/dashboard', false);
    }
}
