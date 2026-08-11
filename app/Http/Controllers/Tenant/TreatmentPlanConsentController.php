<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SignTreatmentPlanConsentRequest;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TreatmentPlanConsentController extends Controller
{
    public function store(
        SignTreatmentPlanConsentRequest $request,
        TreatmentPlan $treatmentPlan,
        TreatmentPlanOption $treatmentPlanOption,
    ): RedirectResponse {
        $this->authorize('signConsent', $treatmentPlanOption);

        abort_unless($treatmentPlanOption->treatment_plan_id === $treatmentPlan->id, 404);

        DB::transaction(function () use ($request, $treatmentPlan, $treatmentPlanOption): void {
            $signatureData = (string) $request->validated('consent_signature');
            $encodedImage = Str::after($signatureData, 'base64,');
            $binary = base64_decode($encodedImage, true);

            abort_if($binary === false, 422, __('Invalid signature data.'));

            $treatmentPlanOption->deleteConsentSignature();

            $path = sprintf(
                'treatment-consents/%d/option-%d-%s.png',
                $treatmentPlan->id,
                $treatmentPlanOption->id,
                now()->format('YmdHis'),
            );

            Storage::disk('local')->put($path, $binary);

            $treatmentPlan->options()->update(['is_selected' => false]);

            $treatmentPlanOption->update([
                'is_selected' => true,
                'consent_signed_at' => now(),
                'consent_signer_name' => $request->validated('consent_signer_name'),
                'consent_statement' => $request->validated('consent_statement'),
                'consent_signature_path' => $path,
                'consent_witnessed_by' => $request->user('staff')->id,
            ]);
        });

        return redirect()
            ->route('tenant.treatment-plans.show', $treatmentPlan)
            ->with('success', __('Informed consent recorded successfully.'));
    }

    public function signature(TreatmentPlan $treatmentPlan, TreatmentPlanOption $treatmentPlanOption): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $treatmentPlan);
        abort_unless($treatmentPlanOption->treatment_plan_id === $treatmentPlan->id, 404);
        abort_unless($treatmentPlanOption->consent_signature_path !== null, 404);

        return Storage::disk('local')->response(
            $treatmentPlanOption->consent_signature_path,
            'consent-signature.png',
            ['Content-Type' => 'image/png'],
        );
    }
}
