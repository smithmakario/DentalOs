<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
    use HasFactory;

    protected $fillable = [
        'id',
        'organization_id',
        'name',
        'slug',
        'is_active',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffBranchAssignment::class, 'tenant_id');
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'organization_id',
            'name',
            'slug',
            'is_active',
        ];
    }
}
