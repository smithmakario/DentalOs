<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\TreatmentPlan;
use App\Support\TenantPortal;
use Illuminate\Support\Collection;

class ClinicOverviewService
{
    /**
     * @return Collection<int, Tenant>
     */
    public function branches(Organization $organization, ?string $branchId = null): Collection
    {
        $branches = $organization->branches()->orderBy('name')->get();

        if ($branchId === null || $branchId === '') {
            return $branches;
        }

        return $branches->where(fn (Tenant $branch): bool => (string) $branch->getTenantKey() === (string) $branchId)->values();
    }

    public function resolveBranch(Organization $organization, ?string $branchId): ?Tenant
    {
        if ($branchId === null || $branchId === '') {
            return $organization->adminBranch;
        }

        return $organization->branches()
            ->whereKey($branchId)
            ->first();
    }

    /**
     * @template T
     *
     * @param  callable(Tenant): Collection<int, T>  $callback
     * @return Collection<int, T>
     */
    public function collectFromBranches(Organization $organization, ?string $branchId, callable $callback): Collection
    {
        $homeTenant = tenancy()->initialized ? tenant() : null;
        $items = collect();

        foreach ($this->branches($organization, $branchId) as $branch) {
            tenancy()->initialize($branch);

            $chunk = $callback($branch);

            foreach ($chunk as $item) {
                $items->push($item);
            }
        }

        $this->restoreTenancy($organization, $homeTenant instanceof Tenant ? $homeTenant : null);

        return $items;
    }

    /**
     * @return array{
     *     branch_count: int,
     *     patient_count: int,
     *     branches_with_patients: int,
     *     today_appointments: int,
     *     upcoming_appointments: int,
     *     treatment_count: int,
     *     service_count: int,
     *     staff_count: int,
     *     invoice_count: int,
     *     today_schedule: Collection<int, array<string, mixed>>,
     * }
     */
    public function dashboard(Organization $organization): array
    {
        $branchCount = $organization->branches()->count();
        $patientCount = 0;
        $branchesWithPatients = 0;
        $todayAppointments = 0;
        $upcomingAppointments = 0;
        $treatmentCount = 0;
        $serviceCount = 0;
        $staffCount = 0;
        $invoiceCount = 0;
        $todaySchedule = collect();

        $homeTenant = tenancy()->initialized ? tenant() : null;

        foreach ($this->branches($organization) as $branch) {
            tenancy()->initialize($branch);

            $branchPatients = Patient::query()->count();
            $patientCount += $branchPatients;

            if ($branchPatients > 0) {
                $branchesWithPatients++;
            }

            $todayAppointments += Appointment::query()->whereDate('scheduled_at', today())->count();
            $upcomingAppointments += Appointment::query()->where('scheduled_at', '>=', now())->count();
            $treatmentCount += TreatmentPlan::query()->count();
            $serviceCount += ClinicService::query()->count();
            $staffCount += Staff::query()->count();
            $invoiceCount += Invoice::query()->count();

            $todaySchedule = $todaySchedule->merge(
                Appointment::query()
                    ->with(['patient', 'provider'])
                    ->whereDate('scheduled_at', today())
                    ->orderBy('scheduled_at')
                    ->get()
                    ->map(fn (Appointment $appointment): array => [
                        'id' => $appointment->id,
                        'scheduled_at' => $appointment->scheduled_at,
                        'status' => $appointment->status,
                        'patient_name' => $appointment->patient?->full_name,
                        'provider_name' => $appointment->provider?->full_name,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                    ]),
            );
        }

        $this->restoreTenancy($organization, $homeTenant instanceof Tenant ? $homeTenant : null);

        return [
            'branch_count' => $branchCount,
            'patient_count' => $patientCount,
            'branches_with_patients' => $branchesWithPatients,
            'today_appointments' => $todayAppointments,
            'upcoming_appointments' => $upcomingAppointments,
            'treatment_count' => $treatmentCount,
            'service_count' => $serviceCount,
            'staff_count' => $staffCount,
            'invoice_count' => $invoiceCount,
            'today_schedule' => $todaySchedule->sortBy('scheduled_at')->values(),
        ];
    }

    private function restoreTenancy(Organization $organization, ?Tenant $homeTenant): void
    {
        $target = $homeTenant ?? $organization->adminBranch;

        if ($target instanceof Tenant) {
            tenancy()->initialize($target);
            // Keep /{clinic}/{branch} URL generation — do not reset to the bare central host.
            TenantPortal::applyUrlRootForCurrentTenant();

            return;
        }

        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }
}
