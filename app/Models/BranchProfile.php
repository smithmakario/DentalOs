<?php

namespace App\Models;

use App\Support\TenantMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'branch_prefix',
        'description',
        'logo_path',
        'contact_email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'timezone',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function logoUrl(): ?string
    {
        return TenantMedia::urlIfExists($this->logo_path);
    }
}
