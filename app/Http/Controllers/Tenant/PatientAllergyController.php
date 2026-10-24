<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePatientAllergyRequest;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Services\ClinicActivityLogService;
use Illuminate\Http\RedirectResponse;

class PatientAllergyController extends Controller
{
    public function __construct(
        private readonly ClinicActivityLogService $activityLog,
    ) {}

    public function store(StorePatientAllergyRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $allergy = $patient->allergies()->create($request->validated());

        $this->activityLog->record(
            'patient_allergy_added',
            __('Allergy ":allergen" added for :patient', [
                'allergen' => $allergy->allergen,
                'patient' => $patient->full_name,
            ]),
            $patient,
            ['allergy_id' => $allergy->id],
        );

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Allergy recorded.'));
    }

    public function destroy(Patient $patient, PatientAllergy $allergy): RedirectResponse
    {
        $this->authorize('update', $patient);

        abort_unless($allergy->patient_id === $patient->id, 404);

        $allergen = $allergy->allergen;
        $allergy->delete();

        $this->activityLog->record(
            'patient_allergy_removed',
            __('Allergy ":allergen" removed for :patient', [
                'allergen' => $allergen,
                'patient' => $patient->full_name,
            ]),
            $patient,
        );

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Allergy removed.'));
    }
}
