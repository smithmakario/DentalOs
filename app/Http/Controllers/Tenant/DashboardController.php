<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $staff = auth('staff')->user();

        return view('tenant.dashboard', [
            'branchName' => tenant('name'),
            'staff' => $staff,
            'patientCount' => Patient::count(),
            'todayAppointments' => Appointment::query()
                ->whereDate('scheduled_at', today())
                ->count(),
            'todaySchedule' => Appointment::query()
                ->with(['patient', 'provider'])
                ->whereDate('scheduled_at', today())
                ->orderBy('scheduled_at')
                ->get(),
            'upcomingAppointments' => Appointment::query()
                ->with(['patient', 'provider'])
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
