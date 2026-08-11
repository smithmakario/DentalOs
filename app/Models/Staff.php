<?php

namespace App\Models;

use App\Enums\StaffPermission;
use App\Enums\StaffRole;
use App\Notifications\StaffResetPassword;
use App\Support\StaffRolePermissions;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Staff extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\StaffFactory> */
    use CanResetPassword;
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'organization_staff_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'specialization',
        'license_number',
        'years_of_experience',
        'avatar_path',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
            'years_of_experience' => 'integer',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function appointmentsAsProvider(): HasMany
    {
        return $this->hasMany(Appointment::class, 'provider_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'provider_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * @param  Builder<Staff>  $query
     * @return Builder<Staff>
     */
    public function scopeProviders(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereIn('role', [StaffRole::Dentist, StaffRole::Hygienist]);
    }

    public function hasPermission(StaffPermission $permission): bool
    {
        return StaffRolePermissions::roleHas($this->role, $permission);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new StaffResetPassword($token));
    }

    public function initials(): string
    {
        return strtoupper(substr($this->first_name, 0, 1).substr($this->last_name, 0, 1));
    }

    public function avatarUrl(): string
    {
        if ($this->avatar_path && Storage::disk('public')->exists($this->avatar_path)) {
            return tenant_asset($this->avatar_path);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&background=dae1ff&color=0050cb';
    }
}

