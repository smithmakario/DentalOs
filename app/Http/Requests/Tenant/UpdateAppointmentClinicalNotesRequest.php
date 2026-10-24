<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentClinicalNotesRequest extends FormRequest
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
            'soap_subjective' => ['nullable', 'string', 'max:5000'],
            'soap_objective' => ['nullable', 'string', 'max:5000'],
            'soap_assessment' => ['nullable', 'string', 'max:5000'],
            'soap_plan' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
