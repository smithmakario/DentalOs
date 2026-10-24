<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePatientVitalRequest;
use App\Models\Patient;
use App\Services\ClinicActivityLogService;
use Illuminate\Http\RedirectResponse;

class PatientVitalController extends Controller
{
    public function __construct(
        private readonly ClinicActivityLogService $activityLog,
    ) {}

    public function store(StorePatientVitalRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $attributes = $request->validated();
        $attributes['recorded_at'] = $attributes['recorded_at'] ?? now();

        $vital = $patient->vitals()->create($attributes);

        $this->activityLog->record(
            'patient_vitals_recorded',
            __('Vitals recorded for :patient', ['patient' => $patient->full_name]),
            $patient,
            ['vital_id' => $vital->id],
        );

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Vitals recorded.'));
    }
}
