<?php

namespace Tests\Feature\Tenant;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use Tests\TenantTestCase;

class DailyReconciliationTest extends TenantTestCase
{
    public function test_staff_can_view_daily_reconciliation_report(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create(['first_name' => 'Reconcile', 'last_name' => 'Patient']);

            $invoice = Invoice::factory()->create([
                'patient_id' => $patient->id,
                'status' => InvoiceStatus::Partial,
                'subtotal' => 10000,
                'tax' => 0,
                'discount' => 500,
                'total' => 9500,
                'amount_paid' => 5000,
                'issued_at' => today(),
            ]);

            Payment::query()->create([
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-TEST-1',
                'amount' => 5000,
                'payment_method' => PaymentMethod::Cash,
                'paid_at' => now(),
            ]);
        });

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/reconciliation?date='.today()->format('Y-m-d')));

        $response->assertOk()
            ->assertViewIs('tenant.reconciliation.show')
            ->assertSee('Daily Reconciliation')
            ->assertSee('5,000.00')
            ->assertSee('Reconcile Patient');
    }

    public function test_staff_can_export_reconciliation_csv(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/reconciliation?date='.today()->format('Y-m-d').'&export=csv'));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}
