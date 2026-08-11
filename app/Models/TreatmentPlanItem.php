<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TreatmentPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatment_plan_option_id',
        'clinic_service_id',
        'procedure_code',
        'name',
        'description',
        'tooth_code',
        'surface',
        'phase_name',
        'phase_order',
        'estimated_cost',
        'sort_order',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'is_completed' => 'boolean',
        ];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlanOption::class, 'treatment_plan_option_id');
    }

    public function clinicService(): BelongsTo
    {
        return $this->belongsTo(ClinicService::class);
    }

    public function treatment(): HasOne
    {
        return $this->hasOne(Treatment::class);
    }
}
