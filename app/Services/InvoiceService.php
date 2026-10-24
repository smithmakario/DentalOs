<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\TreatmentPlanOption;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array{description: string, quantity: int, unit_price: float|int|string, treatment_id?: int|null}>  $items
     */
    public function create(array $attributes, array $items): Invoice
    {
        return DB::transaction(function () use ($attributes, $items): Invoice {
            $invoice = Invoice::query()->create([
                ...$attributes,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => $attributes['status'] ?? InvoiceStatus::Draft,
            ]);

            $this->syncItems($invoice, $items);
            $this->recalculateTotals($invoice);

            return $invoice->fresh(['items', 'patient', 'payments']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array{description: string, quantity: int, unit_price: float|int|string, treatment_id?: int|null}>  $items
     */
    public function update(Invoice $invoice, array $attributes, array $items): Invoice
    {
        return DB::transaction(function () use ($invoice, $attributes, $items): Invoice {
            $invoice->update($attributes);

            $this->syncItems($invoice, $items);
            $this->recalculateTotals($invoice);

            return $invoice->fresh(['items', 'patient', 'payments']);
        });
    }

    public function recordPayment(Invoice $invoice, array $attributes, ?UploadedFile $proof = null): Payment
    {
        return DB::transaction(function () use ($invoice, $attributes, $proof): Payment {
            if ($proof instanceof UploadedFile) {
                $attributes['proof_path'] = $proof->store(
                    'payment-proofs/'.$invoice->id,
                    'public',
                );
            }

            $payment = $invoice->payments()->create([
                ...$attributes,
                'payment_number' => $this->generatePaymentNumber(),
                'paid_at' => $attributes['paid_at'] ?? now(),
            ]);

            $invoice->update([
                'amount_paid' => $invoice->payments()->sum('amount'),
            ]);

            $this->updateStatus($invoice->fresh());

            return $payment;
        });
    }

    public function confirmPayment(Payment $payment, Staff $confirmedBy): Payment
    {
        $payment->update([
            'confirmed_at' => now(),
            'confirmed_by' => $confirmedBy->id,
        ]);

        return $payment->fresh(['invoice', 'confirmedBy']);
    }

    public function void(Invoice $invoice): void
    {
        $invoice->update([
            'status' => InvoiceStatus::Void,
        ]);
    }

    /**
     * @return array{patient_id: int, appointment_id: int|null, notes: string|null, items: list<array{description: string, quantity: int, unit_price: float|int|string, treatment_id?: int|null}>}
     */
    public function prefilledFromTreatmentPlanOption(TreatmentPlanOption $option): array
    {
        $option->loadMissing(['items', 'treatmentPlan']);

        $plan = $option->treatmentPlan;

        return [
            'patient_id' => $plan->patient_id,
            'appointment_id' => null,
            'notes' => __('Invoice from treatment plan: :title — :option', [
                'title' => $plan->title,
                'option' => $option->name,
            ]),
            'items' => $option->items->map(fn ($item): array => [
                'description' => trim($item->name.($item->tooth_code ? " (#{$item->tooth_code})" : '')),
                'quantity' => 1,
                'unit_price' => $item->estimated_cost,
            ])->all(),
        ];
    }

    /**
     * @return array{patient_id: int, appointment_id: int, notes: string|null, items: list<array{description: string, quantity: int, unit_price: float|int|string, treatment_id?: int|null}>}
     */
    public function prefilledFromAppointment(Appointment $appointment): array
    {
        $appointment->loadMissing(['treatments', 'patient']);

        $items = $appointment->treatments
            ->map(fn ($treatment): array => [
                'description' => trim($treatment->name.($treatment->tooth_code ? " (#{$treatment->tooth_code})" : '')),
                'quantity' => 1,
                'unit_price' => $treatment->cost ?? 0,
                'treatment_id' => $treatment->id,
            ])
            ->values()
            ->all();

        if ($items === []) {
            $catalogMatch = ClinicService::query()
                ->where('is_active', true)
                ->where('name', $appointment->title)
                ->first();

            $items = [[
                'description' => $appointment->title ?: __('Consultation'),
                'quantity' => 1,
                'unit_price' => $catalogMatch?->price ?? '',
            ]];
        }

        return [
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'notes' => __('Invoice for appointment on :date', [
                'date' => $appointment->scheduled_at->format('M j, Y'),
            ]),
            'items' => $items,
        ];
    }

    public function applyManualStatus(Invoice $invoice, InvoiceStatus $status): Invoice
    {
        if (! $invoice->allowsManualStatus($status)) {
            throw new InvalidArgumentException(__('This invoice status cannot be applied.'));
        }

        $invoice->update(['status' => $status]);

        return $invoice->fresh(['items', 'patient', 'payments']);
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Y').'-';
        $latest = Invoice::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $latest !== null
            ? ((int) substr($latest, strlen($prefix))) + 1
            : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY-'.now()->format('Y').'-';
        $latest = Payment::query()
            ->where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('payment_number')
            ->value('payment_number');

        $sequence = $latest !== null
            ? ((int) substr($latest, strlen($prefix))) + 1
            : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(Invoice $invoice): void
    {
        $subtotal = $invoice->items()->sum('subtotal');

        $invoice->update([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal + (float) $invoice->tax - (float) $invoice->discount),
        ]);

        $this->updateStatus($invoice->fresh());
    }

    public function updateStatus(Invoice $invoice): void
    {
        if ($invoice->status === InvoiceStatus::Void) {
            return;
        }

        $total = (float) $invoice->total;
        $amountPaid = (float) $invoice->amount_paid;

        if ($total > 0 && $amountPaid >= $total) {
            $invoice->update(['status' => InvoiceStatus::Paid]);

            return;
        }

        if ($amountPaid > 0) {
            $invoice->update(['status' => InvoiceStatus::Partial]);

            return;
        }

        if ($invoice->issued_at !== null && $invoice->status !== InvoiceStatus::Draft) {
            $invoice->update(['status' => InvoiceStatus::Sent]);

            return;
        }
    }

    /**
     * @param  list<array{description: string, quantity: int, unit_price: float|int|string, treatment_id?: int|null}>  $items
     */
    protected function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'treatment_id' => $item['treatment_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
            ]);
        }
    }
}
