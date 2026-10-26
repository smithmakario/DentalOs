<?php

namespace App\Models;

use App\Enums\StaffPermission;
use App\Enums\StaffRole;
use App\Notifications\StaffResetPassword;
use App\Support\StaffRolePermissions;
use App\Support\TenantMedia;
use Database\Factories\StaffFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Staff extends Authenticatable implements CanResetPasswordContract
{
    use BelongsToTenant;

    /** @use HasFactory<StaffFactory> */
    use CanResetPassword;

    use HasFactory;
    use Notifiable;

    protected $table = 'tenant_users';

    protected $fillable = [
        'tenant_id',
        'organization_staff_id',
        'first_name',
        'last_name',
        'email',
        'google_id',
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
        return TenantMedia::urlIfExists($this->avatar_path)
            ?? 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&background=dae1ff&color=0050cb';
    }
}
