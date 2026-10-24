<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreInvoicePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && ($this->user('staff')?->can('recordPayment', $invoice) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Invoice $invoice */
        $invoice = $this->route('invoice');
        $maxAmount = $invoice->balanceDue();
        $method = $this->string('payment_method')->toString();
        $proofRequired = in_array($method, [
            PaymentMethod::Transfer->value,
            PaymentMethod::Pos->value,
        ], true);

        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$maxAmount],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => [
                Rule::requiredIf($proofRequired),
                'nullable',
                'image',
                'max:5120',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof.required' => __('Upload a payment screenshot or receipt for transfer and POS payments.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentAttributes(): array
    {
        return [
            'amount' => $this->input('amount'),
            'payment_method' => PaymentMethod::from($this->string('payment_method')->toString()),
            'payment_reference' => $this->input('payment_reference'),
            'paid_at' => $this->input('paid_at') ?? now(),
            'notes' => $this->input('notes'),
        ];
    }

    public function proofFile(): ?UploadedFile
    {
        return $this->file('proof');
    }
}
