<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TreatmentPlanStatus;
use App\Models\ClinicService;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\TreatmentPlan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('staff')?->can('create', TreatmentPlan::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->optionRules();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => __('patient'),
            'provider_id' => __('provider'),
            'title' => __('case title'),
            'description' => __('clinical notes'),
            'status' => __('status'),
            'options' => __('treatment options'),
            'options.*.name' => __('option name'),
            'options.*.description' => __('option summary'),
            'options.*.items' => __('procedures'),
            'options.*.items.*.name' => __('procedure'),
            'options.*.items.*.procedure_code' => __('procedure code'),
            'options.*.items.*.estimated_cost' => __('cost'),
            'options.*.items.*.clinic_service_id' => __('catalog service'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function optionRules(): array
    {
        return [
            'patient_id' => ['required', Rule::exists(Patient::class, 'id')],
            'provider_id' => ['required', Rule::exists(Staff::class, 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(TreatmentPlanStatus::class)],
            'options' => ['required', 'array', 'min:1'],
            'options.*.name' => ['required', 'string', 'max:255'],
            'options.*.description' => ['nullable', 'string', 'max:2000'],
            'options.*.items' => ['required', 'array', 'min:1'],
            'options.*.items.*.clinic_service_id' => ['nullable', Rule::exists(ClinicService::class, 'id')],
            'options.*.items.*.name' => ['required', 'string', 'max:255'],
            'options.*.items.*.procedure_code' => ['nullable', 'string', 'max:50'],
            'options.*.items.*.description' => ['nullable', 'string', 'max:1000'],
            'options.*.items.*.tooth_code' => ['nullable', 'string', 'max:10'],
            'options.*.items.*.surface' => ['nullable', 'string', 'max:20'],
            'options.*.items.*.phase_name' => ['nullable', 'string', 'max:100'],
            'options.*.items.*.phase_order' => ['nullable', 'integer', 'min:0'],
            'options.*.items.*.estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $treatmentPlan = $this->route('treatment_plan');

        $redirectTo = $treatmentPlan instanceof TreatmentPlan
            ? route('tenant.treatment-plans.edit', $treatmentPlan)
            : route('tenant.treatment-plans.create', array_filter([
                'patient_id' => $this->integer('patient_id') ?: null,
            ]));

        throw (new ValidationException($validator))->redirectTo($redirectTo);
    }
}
