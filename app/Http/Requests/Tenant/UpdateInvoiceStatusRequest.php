<?php

namespace App\Http\Requests\Tenant;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateInvoiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && $this->user('staff')?->can('updateStatus', $invoice);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Invoice|null $invoice */
            $invoice = $this->route('invoice');
            $statusValue = $this->string('status')->toString();

            if (! $invoice instanceof Invoice || $statusValue === '') {
                return;
            }

            $status = InvoiceStatus::tryFrom($statusValue);

            if ($status === null) {
                return;
            }

            if (! $invoice->allowsManualStatus($status)) {
                $validator->errors()->add(
                    'status',
                    __('This invoice cannot be moved to :status from its current status.', [
                        'status' => $status->label(),
                    ]),
                );
            }
        });
    }
}
