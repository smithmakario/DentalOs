<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequestRequest;
use App\Services\RegistrationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GetStartedController extends Controller
{
    public function __construct(
        private RegistrationRequestService $registrationRequestService,
    ) {}

    public function create(): View
    {
        return view('get-started.create');
    }

    public function store(StoreRegistrationRequestRequest $request): RedirectResponse
    {
        $this->registrationRequestService->submit($request->validated());

        return redirect()
            ->route('get-started.thank-you')
            ->with('status', __('Your registration request has been submitted successfully.'));
    }

    public function thankYou(): View
    {
        return view('get-started.thank-you');
    }
}
