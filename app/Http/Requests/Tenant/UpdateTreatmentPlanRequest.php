<?php

namespace App\Http\Requests\Tenant;

use App\Models\TreatmentPlan;

class UpdateTreatmentPlanRequest extends StoreTreatmentPlanRequest
{
    public function authorize(): bool
    {
        $treatmentPlan = $this->route('treatment_plan');

        return $treatmentPlan instanceof TreatmentPlan
            && $this->user('staff')?->can('update', $treatmentPlan);
    }
}
