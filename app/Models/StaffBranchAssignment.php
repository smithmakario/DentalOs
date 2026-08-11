<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffBranchAssignment extends Model
{
    protected $fillable = [
        'staff_member_id',
        'tenant_id',
    ];

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
