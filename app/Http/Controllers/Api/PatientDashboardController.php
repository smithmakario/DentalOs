<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientDashboardController extends Controller
{
    public function index(Request $request)
    {
        $patient = $request->user();

        $patient->load([
            'appointments' => fn ($q) => $q->upcoming()->latest(),
            'invoices' => fn ($q) => $q->unpaid(),
        ]);

        return response()->json([
            'patient' => $patient,
        ]);
    }
}
