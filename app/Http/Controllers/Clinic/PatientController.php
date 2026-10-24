<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class PatientController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $search = $request->string('search')->trim()->toString();
        $branches = $this->overview->branches($organization);

        $patients = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) use ($search) {
                return Patient::query()
                    ->when($search !== '', function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('patient_id_string', 'like', "%{$search}%");
                        });
                    })
                    ->latest()
                    ->get()
                    ->map(fn (Patient $patient): array => [
                        'id' => $patient->id,
                        'patient_id_string' => $patient->patient_id_string,
                        'first_name' => $patient->first_name,
                        'last_name' => $patient->last_name,
                        'full_name' => $patient->full_name,
                        'email' => $patient->email,
                        'phone' => $patient->phone,
                        'insurance_provider' => $patient->insurance_provider,
                        'is_active' => $patient->is_active,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                        'branch_url' => TenantPortal::url($branch, '/patients/'.$patient->id),
                    ]);
            },
        )->sortBy('full_name')->values();

        $branchesWithPatients = $patients->pluck('branch_id')->unique()->count();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;
        $paginator = new LengthAwarePaginator(
            $patients->forPage($page, $perPage)->values(),
            $patients->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('clinic.patients.index', [
            'organization' => $organization,
            'patients' => $paginator,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'search' => $search,
            'totalPatients' => $patients->count(),
            'activePatients' => $patients->where('is_active', true)->count(),
            'branchesWithPatients' => $branchesWithPatients,
        ]);
    }
}
