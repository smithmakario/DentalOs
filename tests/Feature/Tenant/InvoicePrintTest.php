<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Models\Invoice;
use App\Models\Patient;
use Tests\TenantTestCase;

class InvoicePrintTest extends TenantTestCase
{
    public function test_staff_can_open_printable_invoice(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $invoice = $this->tenant->run(function (): Invoice {
            $patient = Patient::factory()->create(['first_name' => 'Print', 'last_name' => 'Me']);

            return Invoice::factory()->create([
                'patient_id' => $patient->id,
            ]);
        });

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/invoices/{$invoice->id}/print"));

        $response->assertOk()
            ->assertViewIs('tenant.invoices.print')
            ->assertSee($invoice->invoice_number)
            ->assertSee('Print Me')
            ->assertSee('Print / Save PDF');
    }
}
