<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;

class TenantMedia
{
    /**
     * Public URL for a file on the tenant public disk.
     * Uses /{org}/{branch}/media/... for path tenancy (stancl /tenancy/assets
     * only initializes by domain and fails on the central host).
     */
    public static function url(string $path, ?Tenant $tenant = null): string
    {
        $path = ltrim($path, '/');
        $tenant ??= tenant() instanceof Tenant ? tenant() : null;

        if ($tenant instanceof Tenant) {
            return TenantPortal::absolute($tenant, '/media/'.$path);
        }

        return tenant_asset($path);
    }

    public static function urlIfExists(?string $path, ?Tenant $tenant = null): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        return self::url($path, $tenant);
    }
}
