<?php

namespace App\Http\Requests\Tenant;

use App\Models\ClinicService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('clinic_service');

        return $service instanceof ClinicService
            && $this->user('staff')?->can('update', $service);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $service = $this->route('clinic_service');

        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('clinic_services', 'code')->ignore($service)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'icon' => ['nullable', 'string', 'max:100', 'alpha_dash'],
            'is_recommended' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
