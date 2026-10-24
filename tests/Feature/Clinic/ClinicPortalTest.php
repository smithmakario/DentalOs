<?php

namespace Tests\Feature\Clinic;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\Patient;
use App\Models\User;
use App\Support\ClinicPortal;
use App\Support\TenantPortal;
use Tests\TenantTestCase;

class ClinicPortalTest extends TenantTestCase
{
    public function test_clinic_login_page_is_available(): void
    {
        $this->get(route('clinic.login'))
            ->assertOk()
            ->assertSee(__('Clinic Sign In'));
    }

    public function test_primary_admin_clinic_login_lands_on_admin_branch_dashboard(): void
    {
        [$user] = $this->provisionPrimaryAdmin();

        $dashboardUrl = TenantPortal::url($this->tenant, '/dashboard');

        $this->assertStringContainsString(
            '/'.$this->organization->slug.'/'.$this->tenant->slug.'/dashboard',
            parse_url($dashboardUrl, PHP_URL_PATH) ?? '',
        );

        $response = $this->post(route('clinic.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($dashboardUrl);
        $this->assertAuthenticated('staff');
        $this->assertTrue(session(ClinicPortal::SESSION_FLAG));
        $this->assertSame($this->organization->id, session(ClinicPortal::SESSION_ORGANIZATION_ID));

        $this->get($dashboardUrl)
            ->assertOk()
            ->assertSee(__('Clinic Overview'))
            ->assertSee($this->organization->name);
    }

    public function test_primary_admin_branch_login_stays_on_branch_dashboard(): void
    {
        [$user] = $this->provisionPrimaryAdmin();

        $this->post($this->tenantUrl('/staff/login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect($this->tenantUrl('/dashboard'));

        $this->get($this->tenantUrl('/dashboard'))
            ->assertOk()
            ->assertSee(__('Clinic Overview'))
            ->assertSee($this->organization->name);
    }

    public function test_clinic_dashboard_path_redirects_to_admin_branch(): void
    {
        [$user] = $this->provisionPrimaryAdmin();

        $this->post(route('clinic.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->get(route('clinic.dashboard'))
            ->assertRedirect(TenantPortal::url($this->tenant, '/dashboard'));
    }

    public function test_primary_admin_sees_cross_branch_patient_count_on_hq_dashboard(): void
    {
        [$user] = $this->provisionPrimaryAdmin();

        $this->tenant->run(function (): void {
            Patient::factory()->create([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
            ]);
        });

        $this->post(route('clinic.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->get(TenantPortal::url($this->tenant, '/dashboard'))
            ->assertOk()
            ->assertSee(__('Clinic Overview'))
            ->assertSee('1')
            ->assertSee(
                '/'.$this->organization->slug.'/'.$this->tenant->slug.'/patients',
                false,
            );
    }

    /**
     * @return array{0: User}
     */
    private function provisionPrimaryAdmin(): array
    {
        $this->organization->update([
            'admin_branch_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'email' => 'primary-admin@clinic.test',
            'password' => 'password',
            'platform_role' => PlatformRole::OrgAdmin,
        ]);

        $this->organization->users()->attach($user->id, [
            'role' => OrganizationRole::Owner->value,
        ]);

        $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => $user->email,
            'password' => 'password',
            'first_name' => 'Primary',
            'last_name' => 'Admin',
        ]);

        return [$user];
    }
}
