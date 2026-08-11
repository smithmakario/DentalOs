<?php

namespace App\Http\Requests\Tenant;

use App\Models\TreatmentPlanOption;
use Illuminate\Foundation\Http\FormRequest;

class SignTreatmentPlanConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $option = $this->route('treatment_plan_option');

        return $option instanceof TreatmentPlanOption
            && $this->user('staff')?->can('signConsent', $option);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'consent_signer_name' => ['required', 'string', 'max:255'],
            'consent_statement' => ['required', 'string', 'max:5000'],
            'consent_signature' => ['required', 'string', 'regex:/^data:image\/png;base64,/'],
            'consent_acknowledged' => ['accepted'],
        ];
    }
}
