<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    protected $table = 'organization_user';

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
        ];
    }
}
