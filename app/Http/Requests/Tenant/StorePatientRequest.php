<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PaymentMethod;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('staff')?->can('create', Patient::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'preferred_payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'insurance_provider' => [
                Rule::requiredIf(fn (): bool => $this->isHmoPayment()),
                'nullable',
                'string',
                'max:150',
            ],
            'insurance_number' => [
                Rule::requiredIf(fn (): bool => $this->isHmoPayment()),
                'nullable',
                'string',
                'max:100',
            ],
            'hmo_plan' => [
                Rule::requiredIf(fn (): bool => $this->isHmoPayment()),
                'nullable',
                'string',
                'max:150',
            ],
            'medical_notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->isHmoPayment()) {
                return;
            }

            foreach (['insurance_provider', 'insurance_number', 'hmo_plan'] as $field) {
                if ($this->string($field)->trim()->isEmpty()) {
                    $validator->errors()->add($field, __('This field is required when HMO is the preferred payment method.'));
                }
            }
        });
    }

    protected function isHmoPayment(): bool
    {
        return $this->string('preferred_payment_method')->toString() === PaymentMethod::Hmo->value;
    }
}
