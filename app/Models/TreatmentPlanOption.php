<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TreatmentPlanOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatment_plan_id',
        'name',
        'description',
        'sort_order',
        'estimated_total',
        'is_selected',
        'consent_signed_at',
        'consent_signer_name',
        'consent_signature_path',
        'consent_statement',
        'consent_witnessed_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_total' => 'decimal:2',
            'is_selected' => 'boolean',
            'consent_signed_at' => 'datetime',
        ];
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class)->orderBy('phase_order')->orderBy('sort_order');
    }

    public function consentWitness(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'consent_witnessed_by');
    }

    public function hasConsent(): bool
    {
        return $this->consent_signed_at !== null && $this->consent_signature_path !== null;
    }

    public function deleteConsentSignature(): void
    {
        if ($this->consent_signature_path !== null) {
            Storage::disk('local')->delete($this->consent_signature_path);
        }
    }
}
