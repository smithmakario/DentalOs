<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    /** @use HasFactory<PatientFactory> */
    use HasApiTokens, HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'patient_id_string',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferred_payment_method',
        'insurance_provider',
        'insurance_number',
        'hmo_plan',
        'medical_notes',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'preferred_payment_method' => PaymentMethod::class,
            'password' => 'hashed',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class)->latest();
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(PatientVital::class)->latest('recorded_at');
    }

    public function labResults(): HasMany
    {
        return $this->hasMany(PatientLabResult::class)->latest('test_date');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function usesHmo(): bool
    {
        return $this->preferred_payment_method === PaymentMethod::Hmo;
    }
}
