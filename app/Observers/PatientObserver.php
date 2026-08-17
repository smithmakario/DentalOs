<?php

namespace App\Observers;

use App\Models\BranchProfile;
use App\Models\Patient;

class PatientObserver
{
    public function creating(Patient $patient): void
    {
        if (! $patient->patient_id_string) {
            $prefix = $this->getBranchPrefix();
            $year = date('Y');
            $nextIncrement = $this->getNextIncrement($year);

            $patient->patient_id_string = sprintf('%s-%s-%05d', $prefix, $year, $nextIncrement);
        }
    }

    private function getBranchPrefix(): string
    {
        $profile = BranchProfile::first();

        if ($profile && $profile->branch_prefix) {
            return strtoupper($profile->branch_prefix);
        }

        // Fallback to TN (Tenant) if no prefix is set
        return 'TN';
    }

    private function getNextIncrement(string $year): int
    {
        // Get the latest patient from this year to calculate the next increment
        $latestPatient = Patient::whereYear('created_at', $year)
            ->whereNotNull('patient_id_string')
            ->orderBy('id', 'desc')
            ->first();

        if (! $latestPatient) {
            return 1;
        }

        // Parse the increment from the latest ID (e.g., WJ-2026-00001)
        $parts = explode('-', $latestPatient->patient_id_string);

        if (count($parts) === 3) {
            return (int) end($parts) + 1;
        }

        // Fallback if parsing fails
        $count = Patient::whereYear('created_at', $year)->count();

        return $count + 1;
    }
}
