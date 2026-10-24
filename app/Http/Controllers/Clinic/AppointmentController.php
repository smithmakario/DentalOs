<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $branches = $this->overview->branches($organization);

        $appointments = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) {
                return Appointment::query()
                    ->with(['patient', 'provider'])
                    ->latest('scheduled_at')
                    ->limit(200)
                    ->get()
                    ->map(fn (Appointment $appointment): array => [
                        'id' => $appointment->id,
                        'scheduled_at' => $appointment->scheduled_at,
                        'status' => $appointment->status,
                        'patient_name' => $appointment->patient?->full_name,
                        'provider_name' => $appointment->provider?->full_name,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                        'branch_url' => TenantPortal::url($branch, '/appointments/'.$appointment->id),
                    ]);
            },
        )->sortByDesc(fn (array $row) => optional($row['scheduled_at'])->timestamp ?? 0)->values();

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $appointments->forPage($page, $perPage)->values(),
            $appointments->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('clinic.appointments.index', [
            'organization' => $organization,
            'appointments' => $paginator,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'totalAppointments' => $appointments->count(),
            'branchesWithAppointments' => $appointments->pluck('branch_id')->unique()->count(),
        ]);
    }
}
