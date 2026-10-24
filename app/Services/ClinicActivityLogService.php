<?php

namespace App\Services;

use App\Models\ClinicActivityLog;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ClinicActivityLogService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
    ): ClinicActivityLog {
        /** @var Staff|null $staff */
        $staff = Auth::guard('staff')->user();

        return ClinicActivityLog::query()->create([
            'staff_id' => $staff?->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}
