<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\ClinicAdminAccess;
use App\Services\ClinicOverviewService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ClinicAdminAccess $clinicAdminAccess,
        private ClinicOverviewService $overview,
    ) {}

    public function __invoke(): View
    {
        $staff = auth('staff')->user();
        $organization = $this->clinicAdminAccess->organization();
        $isClinicHq = $this->clinicAdminAccess->canManageClinic($staff);

        if ($isClinicHq && $organization !== null) {
            $stats = $this->overview->dashboard($organization);

            return view('tenant.dashboard', [
                'isClinicHq' => true,
                'organization' => $organization,
                'branchName' => tenant('name'),
                'staff' => $staff,
                'patientCount' => $stats['patient_count'],
                'todayAppointments' => $stats['today_appointments'],
                'todaySchedule' => $stats['today_schedule'],
                'upcomingAppointments' => collect(),
                'stats' => $stats,
            ]);
        }

        return view('tenant.dashboard', [
            'isClinicHq' => false,
            'organization' => $organization,
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
            'stats' => null,
        ]);
    }
}
