<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Patient;
use Tests\TenantTestCase;

class DashboardTest extends TenantTestCase
{
    public function test_staff_can_view_dashboard(): void
    {
        $staff = $this->createStaff();

        $this->tenant->run(function (): void {
            Patient::factory()->count(3)->create();
            Appointment::factory()->count(2)->create([
                'scheduled_at' => today()->addHours(2),
            ]);
        });

        $response = $this->actingAs($staff, 'staff')->get($this->tenantUrl('/dashboard'));

        $response->assertOk()
            ->assertViewIs('tenant.dashboard')
            ->assertViewHas('patientCount', 3)
            ->assertViewHas('todayAppointments', 2);
    }

    public function test_unauthenticated_staff_cannot_view_dashboard(): void
    {
        $response = $this->get($this->tenantUrl('/dashboard'));

        $response->assertRedirect($this->tenantUrl('/staff/login'));
    }
}
