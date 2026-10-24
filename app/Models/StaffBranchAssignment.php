<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class StaffBranchAssignment extends Model
{
    use CentralConnection;

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
