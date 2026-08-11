<?php

namespace App\Http\Requests\Tenant;

use App\Models\Patient;

class UpdatePatientRequest extends StorePatientRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');

        return $patient instanceof Patient
            && $this->user('staff')?->can('update', $patient);
    }
}
