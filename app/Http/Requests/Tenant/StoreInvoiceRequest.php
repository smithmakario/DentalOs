<?php

namespace App\Http\Requests\Tenant;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('staff')?->can('create', Invoice::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.treatment_id' => ['nullable', 'integer', 'exists:treatments,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function invoiceAttributes(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'appointment_id' => $this->integer('appointment_id') ?: null,
            'tax' => $this->input('tax', 0),
            'discount' => $this->input('discount', 0),
            'notes' => $this->input('notes'),
            'issued_at' => $this->input('issued_at'),
            'due_at' => $this->input('due_at'),
            'status' => InvoiceStatus::from($this->string('status')->toString()),
            'amount_paid' => 0,
        ];
    }

    /**
     * @return list<array{description: string, quantity: int, unit_price: float|int|string, treatment_id?: int|null}>
     */
    public function lineItems(): array
    {
        return collect($this->input('items', []))
            ->map(fn (array $item): array => [
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => $item['unit_price'],
                'treatment_id' => isset($item['treatment_id']) ? (int) $item['treatment_id'] : null,
            ])
            ->all();
    }
}
