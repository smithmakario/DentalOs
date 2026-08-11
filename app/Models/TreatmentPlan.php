<?php

namespace App\Models;

use App\Enums\TreatmentPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'provider_id',
        'title',
        'description',
        'status',
        'estimated_total',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TreatmentPlanStatus::class,
            'estimated_total' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'provider_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(TreatmentPlanOption::class)->orderBy('sort_order');
    }

    public function selectedOption(): ?TreatmentPlanOption
    {
        return $this->options->firstWhere('is_selected', true)
            ?? $this->options->firstWhere(fn (TreatmentPlanOption $option): bool => $option->hasConsent());
    }

    public function hasConsentedOption(): bool
    {
        return $this->options->contains(fn (TreatmentPlanOption $option): bool => $option->hasConsent());
    }

    public function canBeginProcedures(): bool
    {
        $selected = $this->selectedOption();

        return $selected !== null && $selected->hasConsent();
    }
}
