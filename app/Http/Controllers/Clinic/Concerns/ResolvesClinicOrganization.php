<?php

namespace App\Http\Controllers\Clinic\Concerns;

use App\Models\Organization;
use Illuminate\Http\Request;

trait ResolvesClinicOrganization
{
    protected function clinicOrganization(Request $request): Organization
    {
        $organization = $request->attributes->get('clinicOrganization');

        abort_unless($organization instanceof Organization, 404);

        return $organization;
    }
}
