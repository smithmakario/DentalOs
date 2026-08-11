<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectRegistrationRequestRequest;
use App\Models\OrganizationRegistrationRequest;
use App\Services\RegistrationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class RegistrationRequestController extends Controller
{
    public function __construct(
        private RegistrationRequestService $registrationRequestService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', OrganizationRegistrationRequest::class);

        $requests = OrganizationRegistrationRequest::query()
            ->with(['reviewer', 'organization'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = OrganizationRegistrationRequest::query()
            ->where('status', 'pending')
            ->count();

        return view('central.registration-requests.index', [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
            'filters' => [
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function show(OrganizationRegistrationRequest $registrationRequest): View
    {
        $this->authorize('view', $registrationRequest);

        $registrationRequest->load(['reviewer', 'organization']);

        return view('central.registration-requests.show', [
            'registrationRequest' => $registrationRequest,
        ]);
    }

    public function approve(OrganizationRegistrationRequest $registrationRequest): RedirectResponse
    {
        $this->authorize('review', $registrationRequest);

        try {
            $this->registrationRequestService->approve($registrationRequest, auth()->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['review' => $exception->getMessage()]);
        }

        return redirect()
            ->route('registration-requests.show', $registrationRequest)
            ->with('status', __('Registration request approved. The applicant has been notified by email.'));
    }

    public function reject(RejectRegistrationRequestRequest $request, OrganizationRegistrationRequest $registrationRequest): RedirectResponse
    {
        try {
            $this->registrationRequestService->reject(
                $registrationRequest,
                $request->user(),
                $request->validated('rejection_reason'),
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['review' => $exception->getMessage()]);
        }

        return redirect()
            ->route('registration-requests.index')
            ->with('status', __('Registration request rejected. The applicant has been notified by email.'));
    }
}
