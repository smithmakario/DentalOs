<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TreatmentPlanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTreatmentPlanRequest;
use App\Http\Requests\Tenant\UpdateTreatmentPlanRequest;
use App\Models\ClinicService;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TreatmentPlanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TreatmentPlan::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $treatmentPlans = TreatmentPlan::query()
            ->with(['patient', 'provider'])
            ->withCount('options')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('patient', function (Builder $query) use ($search): void {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tenant.treatment-plans.index', [
            'treatmentPlans' => $treatmentPlans,
            'search' => $search,
            'status' => $status,
            'activeCount' => TreatmentPlan::where('status', TreatmentPlanStatus::Active)->count(),
            'draftCount' => TreatmentPlan::where('status', TreatmentPlanStatus::Draft)->count(),
            'completedCount' => TreatmentPlan::where('status', TreatmentPlanStatus::Completed)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TreatmentPlan::class);

        $staff = $request->user('staff');
        $defaultProviderId = $staff !== null && Staff::providers()->whereKey($staff->id)->exists()
            ? $staff->id
            : null;

        return view('tenant.treatment-plans.create', [
            'treatmentPlan' => new TreatmentPlan([
                'status' => TreatmentPlanStatus::Draft,
                'patient_id' => $request->integer('patient_id') ?: null,
                'provider_id' => $defaultProviderId,
            ]),
            'patients' => Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(),
            'providers' => Staff::providers()->orderBy('first_name')->get(),
            'clinicServices' => ClinicService::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTreatmentPlanRequest $request): RedirectResponse
    {
        $this->authorize('create', TreatmentPlan::class);

        $treatmentPlan = $this->persistPlan($request->validated());

        return redirect()
            ->route('tenant.treatment-plans.show', $treatmentPlan)
            ->with('success', __('Treatment plan created successfully.'));
    }

    public function show(TreatmentPlan $treatmentPlan): View
    {
        $this->authorize('view', $treatmentPlan);

        $treatmentPlan->load([
            'patient',
            'provider',
            'options.items.clinicService',
            'options.consentWitness',
        ]);

        return view('tenant.treatment-plans.show', [
            'treatmentPlan' => $treatmentPlan,
            'consentStatement' => $this->defaultConsentStatement($treatmentPlan),
        ]);
    }

    public function edit(TreatmentPlan $treatmentPlan): View
    {
        $this->authorize('update', $treatmentPlan);

        $treatmentPlan->load('options.items');

        return view('tenant.treatment-plans.edit', [
            'treatmentPlan' => $treatmentPlan,
            'patients' => Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(),
            'providers' => Staff::providers()->orderBy('first_name')->get(),
            'clinicServices' => ClinicService::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTreatmentPlanRequest $request, TreatmentPlan $treatmentPlan): RedirectResponse
    {
        $this->authorize('update', $treatmentPlan);

        $treatmentPlan = $this->persistPlan($request->validated(), $treatmentPlan);

        return redirect()
            ->route('tenant.treatment-plans.show', $treatmentPlan)
            ->with('success', __('Treatment plan updated successfully.'));
    }

    public function destroy(TreatmentPlan $treatmentPlan): RedirectResponse
    {
        $this->authorize('delete', $treatmentPlan);

        $treatmentPlan->options->each(fn (TreatmentPlanOption $option) => $option->deleteConsentSignature());
        $treatmentPlan->delete();

        return redirect()
            ->route('tenant.treatment-plans.index')
            ->with('success', __('Treatment plan deleted successfully.'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistPlan(array $data, ?TreatmentPlan $treatmentPlan = null): TreatmentPlan
    {
        return DB::transaction(function () use ($data, $treatmentPlan): TreatmentPlan {
            $optionTotals = collect($data['options'])
                ->map(fn (array $option): float => collect($option['items'])
                    ->sum(fn (array $item): float => (float) ($item['estimated_cost'] ?? 0)));

            $attributes = [
                'patient_id' => $data['patient_id'],
                'provider_id' => $data['provider_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'estimated_total' => $optionTotals->min() ?? 0,
                'approved_at' => TreatmentPlanStatus::from($data['status']) === TreatmentPlanStatus::Active
                    ? ($treatmentPlan?->approved_at ?? now())
                    : null,
            ];

            if ($treatmentPlan === null) {
                $treatmentPlan = TreatmentPlan::query()->create($attributes);
            } else {
                $treatmentPlan->update($attributes);
                $treatmentPlan->options->each(fn (TreatmentPlanOption $option) => $option->deleteConsentSignature());
                $treatmentPlan->options()->delete();
            }

            foreach ($data['options'] as $optionIndex => $optionData) {
                $optionTotal = collect($optionData['items'])
                    ->sum(fn (array $item): float => (float) ($item['estimated_cost'] ?? 0));

                $option = $treatmentPlan->options()->create([
                    'name' => $optionData['name'],
                    'description' => $optionData['description'] ?? null,
                    'sort_order' => $optionIndex,
                    'estimated_total' => $optionTotal,
                    'is_selected' => $optionIndex === 0 && $treatmentPlan->options()->count() === 0,
                ]);

                foreach ($optionData['items'] as $itemIndex => $item) {
                    $option->items()->create([
                        'clinic_service_id' => $item['clinic_service_id'] ?? null,
                        'procedure_code' => $item['procedure_code'] ?? null,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'tooth_code' => $item['tooth_code'] ?? null,
                        'surface' => $item['surface'] ?? null,
                        'phase_name' => $item['phase_name'] ?? __('Phase 1'),
                        'phase_order' => $item['phase_order'] ?? 0,
                        'estimated_cost' => $item['estimated_cost'] ?? 0,
                        'sort_order' => $itemIndex,
                    ]);
                }
            }

            return $treatmentPlan->fresh(['patient', 'provider', 'options.items']);
        });
    }

    private function defaultConsentStatement(TreatmentPlan $treatmentPlan): string
    {
        return __('I, the undersigned patient (or legal guardian), acknowledge that I have been presented with the treatment options above, understand the proposed procedures, associated risks, benefits, alternatives, and estimated costs, and consent to proceed with the selected treatment option.');
    }
}
