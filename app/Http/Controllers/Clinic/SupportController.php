<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    use ResolvesClinicOrganization;

    public function __invoke(Request $request): View
    {
        return view('clinic.support', [
            'organization' => $this->clinicOrganization($request),
            'supportEmail' => config('mail.from.address'),
            'supportName' => config('mail.from.name', config('app.name')),
        ]);
    }
}
