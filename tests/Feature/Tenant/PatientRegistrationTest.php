<?php

namespace Tests\Feature\Tenant;

use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Models\Patient;
use Tests\TenantTestCase;

class PatientRegistrationTest extends TenantTestCase
{
    public function test_receptionist_can_register_patient_with_hmo_details(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/patients'), [
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'phone' => '08012345678',
            'preferred_payment_method' => PaymentMethod::Hmo->value,
            'insurance_provider' => 'Reliance HMO',
            'hmo_plan' => 'Corporate Plan',
            'insurance_number' => 'REL-998877',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->tenant->run(function (): void {
            $patient = Patient::query()->where('last_name', 'Okafor')->first();
            $this->assertNotNull($patient);
            $this->assertSame(PaymentMethod::Hmo, $patient->preferred_payment_method);
            $this->assertSame('Reliance HMO', $patient->insurance_provider);
            $this->assertSame('Corporate Plan', $patient->hmo_plan);
            $this->assertSame('REL-998877', $patient->insurance_number);
        });
    }

    public function test_hmo_details_are_required_when_hmo_is_selected(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/patients'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'preferred_payment_method' => PaymentMethod::Hmo->value,
        ]);

        $response->assertSessionHasErrors(['insurance_provider', 'insurance_number', 'hmo_plan']);
    }

    public function test_patient_can_register_with_cash_payment_without_hmo_fields(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/patients'), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'preferred_payment_method' => PaymentMethod::Cash->value,
        ]);

        $response->assertRedirect();

        $this->tenant->run(function (): void {
            $patient = Patient::query()->where('last_name', 'Smith')->first();
            $this->assertNotNull($patient);
            $this->assertSame(PaymentMethod::Cash, $patient->preferred_payment_method);
            $this->assertNull($patient->insurance_provider);
        });
    }
}
