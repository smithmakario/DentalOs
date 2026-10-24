<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientVitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recorded_at' => ['nullable', 'date'],
            'blood_pressure' => ['nullable', 'string', 'max:50'],
            'heart_rate' => ['nullable', 'integer', 'min:1', 'max:300'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'weight' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
