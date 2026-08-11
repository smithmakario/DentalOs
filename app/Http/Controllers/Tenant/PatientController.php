<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePatientRequest;
use App\Http\Requests\Tenant\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Patient::class);

        $search = $request->string('search')->trim()->toString();

        $patients = Patient::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalPatients = Patient::count();
        $activePatients = Patient::where('is_active', true)->count();
        $newThisMonth = Patient::where('created_at', '>=', now()->startOfMonth())->count();

        return view('tenant.patients.index', [
            'patients' => $patients,
            'search' => $search,
            'totalPatients' => $totalPatients,
            'activePatients' => $activePatients,
            'newThisMonth' => $newThisMonth,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        return view('tenant.patients.create', [
            'patient' => new Patient(['is_active' => true]),
        ]);
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $this->authorize('create', Patient::class);

        $patient = Patient::create($this->patientAttributes($request));

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Patient record created successfully.'));
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $patient->loadCount(['appointments', 'treatmentPlans', 'invoices', 'documents']);
        $patient->load([
            'appointments' => fn ($query) => $query->with('provider')->latest('scheduled_at')->limit(5),
            'treatmentPlans' => fn ($query) => $query->with('provider')->latest()->limit(5),
            'documents' => fn ($query) => $query->with('uploader'),
            'invoices' => fn ($query) => $query->latest()->limit(5),
        ]);

        return view('tenant.patients.show', [
            'patient' => $patient,
        ]);
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        return view('tenant.patients.edit', [
            'patient' => $patient,
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $patient->update($this->patientAttributes($request));

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Patient record updated successfully.'));
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete', $patient);

        $patient->delete();

        return redirect()
            ->route('tenant.patients.index')
            ->with('success', __('Patient record archived successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function patientAttributes(StorePatientRequest $request): array
    {
        $attributes = [
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->string('preferred_payment_method')->toString() !== \App\Enums\PaymentMethod::Hmo->value) {
            $attributes['insurance_provider'] = null;
            $attributes['insurance_number'] = null;
            $attributes['hmo_plan'] = null;
        }

        return $attributes;
    }
}
