<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Tests\TenantTestCase;

class ClinicalAppTest extends TenantTestCase
{
    public function test_staff_can_view_branch_login_page(): void
    {
        $response = $this->get($this->tenantUrl('/staff/login'));

        $response->assertOk();
        $response->assertSee(__('Branch Sign In'));
        $response->assertSee('Test Branch');
    }

    public function test_staff_can_authenticate_and_view_dashboard(): void
    {
        $staff = $this->createStaff([
            'email' => 'dentist@branch.test',
            'password' => Hash::make('password'),
            'role' => StaffRole::Dentist,
        ]);

        $response = $this->post($this->tenantUrl('/staff/login'), [
            'email' => $staff->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($this->tenantUrl('/dashboard'));

        $dashboard = $this->actingAs($staff, 'staff')->get($this->tenantUrl('/dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee(__('Branch Overview'));
    }

    public function test_dentist_can_list_and_view_patients(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
        ]));

        $index = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/patients'));

        $index->assertOk();
        $index->assertSee('Alice Johnson');

        $show = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/patients/'.$patient->id));

        $show->assertOk();
        $show->assertSee(__('Personal Information'));
    }

    public function test_dentist_can_create_patient(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $response = $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl('/patients'), [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.smith@example.test',
                'phone' => '+1 555 9999',
                'preferred_payment_method' => PaymentMethod::Pos->value,
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->tenant->run(function (): void {
            $this->assertDatabaseHas('patients', [
                'email' => 'bob.smith@example.test',
            ]);
        });
    }

    public function test_hygienist_cannot_create_patient(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Hygienist]);

        $response = $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl('/patients'), [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.smith@example.test',
                'is_active' => true,
            ]);

        $response->assertForbidden();
    }

    public function test_dentist_can_schedule_appointment(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        [$patient, $provider] = $this->tenant->run(function (): array {
            return [
                Patient::factory()->create(),
                Staff::factory()->create(['role' => StaffRole::Dentist]),
            ];
        });

        $scheduledAt = now()->addDays(3)->setTime(10, 0);

        $service = $this->tenant->run(fn () => \App\Models\ClinicService::query()->create([
            'code' => 'TEST-001',
            'name' => 'Routine Checkup',
            'category' => 'Preventive',
            'price' => 80000,
            'duration_minutes' => 30,
            'is_active' => true,
        ]));

        $response = $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl('/appointments'), [
                'patient_id' => $patient->id,
                'provider_id' => $provider->id,
                'service_id' => $service->id,
                'scheduled_date' => $scheduledAt->format('Y-m-d'),
                'scheduled_time' => $scheduledAt->format('H:i'),
                'notes' => 'Routine visit',
            ]);

        $response->assertRedirect();

        $this->tenant->run(function (): void {
            $this->assertDatabaseHas('appointments', [
                'title' => 'Routine Checkup',
            ]);
        });
    }

    public function test_appointment_index_lists_scheduled_visits(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create(['first_name' => 'Casey', 'last_name' => 'Lee']);
            $provider = Staff::factory()->create(['role' => StaffRole::Dentist]);

            Appointment::query()->create([
                'patient_id' => $patient->id,
                'provider_id' => $provider->id,
                'title' => 'Consultation',
                'scheduled_at' => now()->addDay(),
                'duration_minutes' => 45,
                'status' => AppointmentStatus::Confirmed,
            ]);
        });

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/appointments'));

        $response->assertOk();
        $response->assertSee('Casey Lee');
        $response->assertSee('Consultation');
    }
}
