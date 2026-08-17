<?php

namespace App\Models;

use App\Enums\StaffRole;
use Database\Factories\StaffMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    /** @use HasFactory<StaffMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
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
        'has_global_branch_access',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
            'years_of_experience' => 'integer',
            'has_global_branch_access' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branchAssignments(): HasMany
    {
        return $this->hasMany(StaffBranchAssignment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(StaffPerformanceReview::class, 'staff_member_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(StaffLeaveRequest::class);
    }
}
