<?php

namespace Tests\Feature\Tenant;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class TransactionTest extends TenantTestCase
{
    public function test_transactions_page_lists_payments_with_method(): void
    {
        $this->organization->update(['admin_branch_id' => $this->tenant->id]);

        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create([
                'first_name' => 'Amina',
                'last_name' => 'Okoro',
            ]);

            $invoice = Invoice::factory()->create([
                'patient_id' => $patient->id,
                'invoice_number' => 'INV-2026-0099',
                'status' => InvoiceStatus::Partial,
                'total' => 200,
                'amount_paid' => 100,
            ]);

            Payment::query()->create([
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-2026-0099',
                'amount' => 100,
                'payment_method' => PaymentMethod::Cash,
                'paid_at' => now(),
            ]);
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/transactions'))
            ->assertOk()
            ->assertSee(__('Transactions'))
            ->assertSee('Amina Okoro')
            ->assertSee(__('Cash'))
            ->assertSee('PAY-2026-0099');
    }

    public function test_transfer_payment_requires_proof_upload(): void
    {
        Storage::fake('public');

        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $invoiceId = $this->tenant->run(function () use ($patient): int {
            return Invoice::factory()->create([
                'patient_id' => $patient->id,
                'status' => InvoiceStatus::Sent,
                'total' => 150,
                'amount_paid' => 0,
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/invoices/{$invoiceId}/payments"), [
                'amount' => 150,
                'payment_method' => PaymentMethod::Transfer->value,
            ])
            ->assertSessionHasErrors('proof');

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/invoices/{$invoiceId}/payments"), [
                'amount' => 150,
                'payment_method' => PaymentMethod::Transfer->value,
                'proof' => UploadedFile::fake()->image('transfer.png'),
            ])
            ->assertRedirect($this->tenantUrl("/invoices/{$invoiceId}"));

        $this->tenant->run(function () use ($invoiceId): void {
            $payment = Payment::query()->where('invoice_id', $invoiceId)->first();

            $this->assertNotNull($payment);
            $this->assertSame(PaymentMethod::Transfer, $payment->payment_method);
            $this->assertNotNull($payment->proof_path);
            $this->assertFalse($payment->isConfirmed());
        });
    }

    public function test_staff_can_confirm_recorded_payment(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        [$invoiceId, $paymentId] = $this->tenant->run(function (): array {
            $patient = Patient::factory()->create();
            $invoice = Invoice::factory()->create([
                'patient_id' => $patient->id,
                'status' => InvoiceStatus::Paid,
                'total' => 80,
                'amount_paid' => 80,
            ]);

            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-2026-0080',
                'amount' => 80,
                'payment_method' => PaymentMethod::Cash,
                'paid_at' => now(),
            ]);

            return [$invoice->id, $payment->id];
        });

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/invoices/{$invoiceId}/payments/{$paymentId}/confirm"))
            ->assertRedirect($this->tenantUrl("/invoices/{$invoiceId}"));

        $this->tenant->run(function () use ($paymentId, $staff): void {
            $payment = Payment::query()->findOrFail($paymentId);

            $this->assertTrue($payment->isConfirmed());
            $this->assertSame($staff->id, $payment->confirmed_by);
        });
    }

    public function test_patient_show_includes_payment_history(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $patientId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create([
                'first_name' => 'Kemi',
                'last_name' => 'Ade',
            ]);

            $invoice = Invoice::factory()->create([
                'patient_id' => $patient->id,
                'status' => InvoiceStatus::Paid,
                'total' => 50,
                'amount_paid' => 50,
            ]);

            Payment::query()->create([
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-2026-0050',
                'amount' => 50,
                'payment_method' => PaymentMethod::Pos,
                'paid_at' => now(),
            ]);

            return $patient->id;
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/patients/'.$patientId))
            ->assertOk()
            ->assertSee(__('Payment history'))
            ->assertSee(__('POS'))
            ->assertSee('50.00');
    }
}
