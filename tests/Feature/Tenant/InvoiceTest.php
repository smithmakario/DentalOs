<?php

namespace Tests\Feature\Tenant;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Enums\TreatmentPlanStatus;
use App\Models\ClinicService;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanOption;
use Tests\TenantTestCase;

class InvoiceTest extends TenantTestCase
{
    public function test_receptionist_can_create_invoice_and_record_payment(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $createResponse = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/invoices'), [
            'patient_id' => $patient->id,
            'status' => InvoiceStatus::Sent->value,
            'tax' => 0,
            'discount' => 0,
            'issued_at' => today()->toDateString(),
            'due_at' => today()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => 150,
                ],
            ],
        ]);

        $invoiceId = $this->tenant->run(function () use ($patient): int {
            return (int) Invoice::query()->where('patient_id', $patient->id)->value('id');
        });

        $createResponse->assertRedirect($this->tenantUrl("/invoices/{$invoiceId}"));

        $this->tenant->run(function () use ($patient): void {
            $invoice = Invoice::query()->where('patient_id', $patient->id)->first();
            $this->assertNotNull($invoice);
            $this->assertSame('150.00', $invoice->total);
            $this->assertSame(InvoiceStatus::Sent, $invoice->status);
            $this->assertCount(1, $invoice->items);
        });

        $paymentResponse = $this->actingAs($staff, 'staff')->post($this->tenantUrl("/invoices/{$invoiceId}/payments"), [
            'amount' => 150,
            'payment_method' => PaymentMethod::Cash->value,
        ]);

        $paymentResponse->assertRedirect($this->tenantUrl("/invoices/{$invoiceId}"));

        $this->tenant->run(function () use ($invoiceId): void {
            $invoice = Invoice::query()->findOrFail($invoiceId);
            $this->assertSame('150.00', $invoice->amount_paid);
            $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        });
    }

    public function test_dentist_can_view_invoices_but_not_create(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $invoiceId = $this->tenant->run(function () use ($patient): int {
            return Invoice::factory()->create([
                'patient_id' => $patient->id,
                'invoice_number' => 'INV-2026-0001',
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/invoices'))
            ->assertOk()
            ->assertSee('INV-2026-0001');

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl('/invoices'), [
                'patient_id' => $patient->id,
                'status' => InvoiceStatus::Draft->value,
                'items' => [
                    ['description' => 'Test', 'quantity' => 1, 'unit_price' => 50],
                ],
            ])
            ->assertForbidden();
    }

    public function test_invoice_can_be_created_from_consented_treatment_plan_option(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $provider = $this->createStaff(['role' => StaffRole::Dentist, 'email' => 'dentist-'.uniqid().'@example.com']);

        [$patientId, $optionId] = $this->tenant->run(function () use ($provider): array {
            $patient = Patient::factory()->create();
            $service = ClinicService::query()->create([
                'code' => 'D6010',
                'name' => 'Implant',
                'category' => 'Surgery',
                'price' => 2500,
                'is_active' => true,
            ]);

            $plan = TreatmentPlan::query()->create([
                'patient_id' => $patient->id,
                'provider_id' => $provider->id,
                'title' => 'Missing tooth restoration',
                'status' => TreatmentPlanStatus::Active,
                'estimated_total' => 2500,
            ]);

            $option = TreatmentPlanOption::query()->create([
                'treatment_plan_id' => $plan->id,
                'name' => 'Implant Option',
                'estimated_total' => 2500,
                'is_selected' => true,
                'consent_signed_at' => now(),
                'consent_signer_name' => $patient->full_name,
                'consent_signature_path' => 'consents/test.png',
                'consent_statement' => 'I agree to treatment.',
            ]);

            $option->items()->create([
                'clinic_service_id' => $service->id,
                'name' => 'Surgical Implant',
                'procedure_code' => 'D6010',
                'estimated_cost' => 2500,
                'phase_name' => 'Phase 1',
                'phase_order' => 0,
                'sort_order' => 0,
            ]);

            return [$patient->id, $option->id];
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/invoices/create?treatment_plan_option_id={$optionId}"))
            ->assertOk()
            ->assertSee('Surgical Implant');

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/invoices'), [
            'patient_id' => $patientId,
            'status' => InvoiceStatus::Sent->value,
            'items' => [
                [
                    'description' => 'Surgical Implant',
                    'quantity' => 1,
                    'unit_price' => 2500,
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->tenant->run(function () use ($patientId): void {
            $invoice = Invoice::query()->where('patient_id', $patientId)->first();
            $this->assertNotNull($invoice);
            $this->assertSame('2500.00', $invoice->total);
        });
    }

    public function test_cannot_prefill_invoice_from_option_without_consent(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $provider = $this->createStaff(['role' => StaffRole::Dentist, 'email' => 'dentist2-'.uniqid().'@example.com']);

        $optionId = $this->tenant->run(function () use ($provider): int {
            $patient = Patient::factory()->create();

            $plan = TreatmentPlan::query()->create([
                'patient_id' => $patient->id,
                'provider_id' => $provider->id,
                'title' => 'Test plan',
                'status' => TreatmentPlanStatus::Draft,
                'estimated_total' => 500,
            ]);

            return TreatmentPlanOption::query()->create([
                'treatment_plan_id' => $plan->id,
                'name' => 'Option A',
                'estimated_total' => 500,
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/invoices/create?treatment_plan_option_id={$optionId}"))
            ->assertForbidden();
    }
}
