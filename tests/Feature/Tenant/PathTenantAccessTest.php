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
use App\Support\StaffAuth;
use App\Support\TenantPortal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PathTenantAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    public function test_branch_without_domain_is_reachable_via_clinic_and_branch_path(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $suffix = Str::lower(fake()->unique()->lexify('????'));
        $clinicName = 'Path Clinic '.$suffix;

        $this->actingAs($user)->post(route('clinics.store'), [
            'name' => $clinicName,
            'type' => 'single',
            'email' => 'contact@path-'.$suffix.'.test',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@path-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()->where('name', $clinicName)->firstOrFail();
        $tenant = $organization->branches()->firstOrFail();

        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => $organization->type,
            'is_active' => true,
        ]);

        app(ClinicSubscriptionAccess::class)->assignPlan(
            $organization,
            $plan,
            BillingCycle::Monthly,
        );

        $loginUrl = TenantPortal::loginUrl($tenant);

        $this->assertStringContainsString(
            '/'.$organization->slug.'/'.$tenant->slug.'/staff/login',
            parse_url($loginUrl, PHP_URL_PATH) ?? '',
        );

        $this->get($loginUrl)->assertOk()
            ->assertSee(__('Welcome to :clinic', ['clinic' => $organization->name]));
    }

    public function test_authenticated_staff_opening_path_login_stays_on_branch_not_platform_login(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $suffix = Str::lower(fake()->unique()->lexify('????'));

        $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Share Clinic '.$suffix,
            'type' => 'single',
            'email' => 'contact@share-'.$suffix.'.test',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@share-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()->where('email', 'contact@share-'.$suffix.'.test')->firstOrFail();
        $tenant = $organization->branches()->firstOrFail();

        $plan = SubscriptionPlan::factory()->create([
            'organization_type' => $organization->type,
            'is_active' => true,
        ]);

        app(ClinicSubscriptionAccess::class)->assignPlan(
            $organization,
            $plan,
            BillingCycle::Monthly,
        );

        $staff = $tenant->run(fn (): Staff => Staff::query()->where('email', 'admin@share-'.$suffix.'.test')->firstOrFail());

        $loginUrl = TenantPortal::loginUrl($tenant);

        $this->get($loginUrl)
            ->assertOk()
            ->assertSee(__('Welcome to :clinic', ['clinic' => $organization->name]));

        $this->withSession([
            StaffAuth::SESSION_EMAIL => strtolower($staff->email),
        ])->actingAs($staff, 'staff')
            ->get($loginUrl)
            ->assertOk()
            ->assertSee(__('Welcome to :clinic', ['clinic' => $organization->name]))
            ->assertSee(__('Welcome back, :name.', ['name' => $staff->first_name]));

        $this->assertSame(StaffRole::ClinicAdmin, $staff->role);
    }

    public function test_path_tenant_can_view_staff_show_after_create(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $suffix = Str::lower(fake()->unique()->lexify('????'));

        $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Bind Clinic '.$suffix,
            'type' => 'single',
            'email' => 'contact@bind-'.$suffix.'.test',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@bind-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()->where('email', 'contact@bind-'.$suffix.'.test')->firstOrFail();
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
            ->where('email', 'admin@bind-'.$suffix.'.test')
            ->firstOrFail());

        $staffStoreUrl = TenantPortal::url($tenant, '/staff');

        $create = $this->actingAs($admin, 'staff')->post($staffStoreUrl, [
            'first_name' => 'Path',
            'last_name' => 'Dentist',
            'email' => 'dentist@bind-'.$suffix.'.test',
            'password' => 'password123',
            'role' => StaffRole::Dentist->value,
            'is_active' => true,
        ]);

        $create->assertRedirect();
        $create->assertSessionDoesntHaveErrors();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $memberId = $tenant->run(fn (): ?int => Staff::query()
            ->where('email', 'dentist@bind-'.$suffix.'.test')
            ->value('id'));

        $this->assertNotNull($memberId);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $this->actingAs($admin, 'staff')
            ->get(TenantPortal::url($tenant, '/staff/'.$memberId))
            ->assertOk()
            ->assertSee('dentist@bind-'.$suffix.'.test');
    }

    public function test_path_tenant_staff_show_works_when_staff_has_central_member_link(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $suffix = Str::lower(fake()->unique()->lexify('????'));

        $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Link Clinic '.$suffix,
            'type' => 'single',
            'email' => 'contact@link-'.$suffix.'.test',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@link-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()->where('email', 'contact@link-'.$suffix.'.test')->firstOrFail();
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

        $centralMember = $organization->staffMembers()
            ->where('email', 'admin@link-'.$suffix.'.test')
            ->firstOrFail();

        $admin = $tenant->run(function () use ($centralMember, $suffix): Staff {
            $staff = Staff::query()->where('email', 'admin@link-'.$suffix.'.test')->firstOrFail();
            $staff->update(['organization_staff_id' => $centralMember->id]);

            return $staff->fresh();
        });

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        // EnsureStaffBranchAccess resolves the central StaffMember; that must not
        // tear down tenancy before Staff::loadCount runs on the show page.
        $this->actingAs($admin, 'staff')
            ->get(TenantPortal::url($tenant, '/staff/'.$admin->id))
            ->assertOk()
            ->assertSee('admin@link-'.$suffix.'.test');
    }
}
