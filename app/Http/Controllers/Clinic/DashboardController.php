<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Services\ClinicOverviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function __invoke(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $stats = $this->overview->dashboard($organization);

        return view('clinic.dashboard', [
            'organization' => $organization,
            'staff' => auth('staff')->user(),
            'stats' => $stats,
        ]);
    }
}
