<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Http\Requests\CompleteOnboardingRequest;
use App\Models\OrganizationRegistrationRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ClinicOnboardingService;
use App\Services\RegistrationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OnboardingController extends Controller
{
    public function __construct(
        private RegistrationRequestService $registrationRequestService,
        private ClinicOnboardingService $clinicOnboardingService,
        private AuditLogService $auditLogService,
    ) {}

    public function show(string $token): View
    {
        $registrationRequest = $this->resolveOnboardableRequest($token);

        return view('get-started.onboarding', [
            'registrationRequest' => $registrationRequest,
        ]);
    }

    public function store(CompleteOnboardingRequest $request, string $token): RedirectResponse
    {
        $registrationRequest = $this->resolveOnboardableRequest($token);

        $data = $request->validated();
        $data['name'] = $data['name'] ?? $registrationRequest->name;
        $data['email'] = $data['email'] ?? $registrationRequest->email;
        $data['phone'] = $data['phone'] ?? $registrationRequest->phone;
        $data['address'] = $data['address'] ?? $registrationRequest->location;

        $organization = $this->clinicOnboardingService->onboard($data);

        $this->registrationRequestService->markCompleted($registrationRequest, $organization);

        $this->auditLogService->record(
            AuditAction::ClinicOnboarded,
            __('Onboarded clinic :name via registration request.', ['name' => $organization->name]),
            $organization,
            $organization,
            ['registration_request_id' => $registrationRequest->id],
        );

        $admin = User::query()->where('email', $data['admin_email'])->firstOrFail();

        Auth::login($admin);

        return redirect()
            ->route('subscriptions.checkout', $organization)
            ->with('status', __('Clinic created successfully. Choose a subscription plan to get started.'));
    }

    private function resolveOnboardableRequest(string $token): OrganizationRegistrationRequest
    {
        $registrationRequest = $this->registrationRequestService->findByOnboardingToken($token);

        if ($registrationRequest === null || ! $registrationRequest->canOnboard()) {
            throw new NotFoundHttpException;
        }

        return $registrationRequest;
    }
}
