<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ClinicService extends Model
{
    use BelongsToTenant;
    /** @use HasFactory<\Database\Factories\ClinicServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'price',
        'duration_minutes',
        'icon',
        'is_recommended',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_recommended' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
