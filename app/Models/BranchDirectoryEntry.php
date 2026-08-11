<?php

namespace App\Models;

use App\Enums\DirectoryResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchDirectoryEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'resource_type',
        'local_id',
        'display_name',
        'email',
        'phone',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'resource_type' => DirectoryResourceType::class,
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
