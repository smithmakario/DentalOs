<?php

namespace Tests\Feature\Tenant;

use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Tenant;
use App\Services\PrimaryClinicAdminProvisioner;
use App\Support\ClinicPortal;
use App\Support\StaffAuth;
use App\Support\TenantPortal;
use Illuminate\Support\Str;
use Tests\TenantTestCase;

class PrimaryAdminCrossBranchTest extends TenantTestCase
{
    private Tenant $satellite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization->update([
            'admin_branch_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $suffix = Str::lower(Str::random(6));
        $satelliteId = 'sat-'.$suffix;

        $this->satellite = Tenant::query()->create([
            'id' => $satelliteId,
            'organization_id' => $this->organization->id,
            'name' => 'Satellite Branch',
            'slug' => $satelliteId,
            'is_active' => true,
        ]);

        $this->satellite->domains()->create(['domain' => $satelliteId.'.localhost']);
    }

    protected function tearDown(): void
    {
        if (isset($this->satellite)) {
            if (tenancy()->initialized) {
                tenancy()->end();
            }

            Tenant::withoutEvents(function (): void {
                $this->satellite->domains()->delete();
                $this->satellite->delete();
            });
        }

        parent::tearDown();
    }

    public function test_primary_admin_can_create_patient_on_another_branch(): void
    {
        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'primary@clinic.test',
            'password' => 'password',
            'first_name' => 'Primary',
            'last_name' => 'Admin',
        ]);

        app(PrimaryClinicAdminProvisioner::class)->syncToAllBranches($this->organization->fresh());

        $createUrl = TenantPortal::url($this->satellite, '/patients/create');
        $storeUrl = TenantPortal::url($this->satellite, '/patients');

        $this->withSession([
            StaffAuth::SESSION_EMAIL => strtolower($admin->email),
            ClinicPortal::SESSION_FLAG => true,
            ClinicPortal::SESSION_ORGANIZATION_ID => $this->organization->id,
        ])->actingAs($admin, 'staff')
            ->get($createUrl)
            ->assertOk()
            ->assertSee(__('Working branch'))
            ->assertSee($this->satellite->name);

        $this->withSession([
            StaffAuth::SESSION_EMAIL => strtolower($admin->email),
            ClinicPortal::SESSION_FLAG => true,
            ClinicPortal::SESSION_ORGANIZATION_ID => $this->organization->id,
        ])->actingAs($admin, 'staff')
            ->post($storeUrl, [
                'first_name' => 'Cross',
                'last_name' => 'Branch',
                'phone' => '08011112222',
                'preferred_payment_method' => PaymentMethod::Cash->value,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->satellite->run(function (): void {
            $this->assertTrue(
                Patient::query()->where('first_name', 'Cross')->where('last_name', 'Branch')->exists(),
            );
        });

        $this->tenant->run(function (): void {
            $this->assertFalse(
                Patient::query()->where('first_name', 'Cross')->where('last_name', 'Branch')->exists(),
            );
        });
    }

    public function test_branch_staff_cannot_operate_on_another_branch(): void
    {
        $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'primary@clinic.test',
            'password' => 'password',
        ]);

        app(PrimaryClinicAdminProvisioner::class)->syncToAllBranches($this->organization->fresh());

        $receptionist = $this->createStaff([
            'role' => StaffRole::Receptionist,
            'email' => 'front@clinic.test',
            'password' => 'password',
        ]);

        $this->withSession([
            StaffAuth::SESSION_EMAIL => strtolower($receptionist->email),
        ])->actingAs($receptionist, 'staff')
            ->get(TenantPortal::url($this->satellite, '/patients/create'))
            ->assertRedirect();

        $this->satellite->run(function (): void {
            $this->assertFalse(
                Staff::query()->where('email', 'front@clinic.test')->exists(),
            );
        });
    }

    public function test_creating_a_branch_syncs_primary_admin_staff(): void
    {
        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'creator@clinic.test',
            'password' => 'password',
            'first_name' => 'Creator',
            'last_name' => 'Admin',
        ]);

        $branchSlug = 'west-'.Str::lower(Str::random(4));

        $this->actingAs($admin, 'staff')
            ->post($this->tenantUrl('/clinic-branches'), [
                'branch_name' => 'West Wing',
                'branch_slug' => $branchSlug,
            ])
            ->assertRedirect($this->tenantUrl('/clinic-branches'));

        $branch = Tenant::query()->where('slug', $branchSlug)->firstOrFail();

        try {
            $branch->run(function () use ($admin): void {
                $synced = Staff::query()->where('email', $admin->email)->first();
                $this->assertNotNull($synced);
                $this->assertSame(StaffRole::ClinicAdmin, $synced->role);
            });
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }

            try {
                $branch->database()->manager()->deleteDatabase($branch);
            } catch (\Throwable) {
                //
            }

            Tenant::withoutEvents(function () use ($branch): void {
                $branch->domains()->delete();
                $branch->delete();
            });
        }
    }
}
