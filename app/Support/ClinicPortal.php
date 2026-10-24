<?php

namespace App\Support;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ClinicPortal
{
    public const SESSION_FLAG = 'clinic_portal';

    public const SESSION_ORGANIZATION_ID = 'clinic_organization_id';

    public static function mark(Request $request, Organization $organization): void
    {
        $request->session()->put(self::SESSION_FLAG, true);
        $request->session()->put(self::SESSION_ORGANIZATION_ID, $organization->id);
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget([
            self::SESSION_FLAG,
            self::SESSION_ORGANIZATION_ID,
        ]);
    }

    public static function isActive(?Request $request = null): bool
    {
        $request ??= app()->bound('request') ? request() : null;

        return $request instanceof Request
            && $request->session()->get(self::SESSION_FLAG) === true;
    }

    public static function organizationId(?Request $request = null): ?int
    {
        $request ??= app()->bound('request') ? request() : null;

        if (! $request instanceof Request) {
            return null;
        }

        $organizationId = $request->session()->get(self::SESSION_ORGANIZATION_ID);

        return is_numeric($organizationId) ? (int) $organizationId : null;
    }

    /**
     * Keep named /clinic routes on the central host (not /{org}/{branch}).
     */
    public static function applyCentralUrlRoot(?Request $request = null): void
    {
        URL::forceRootUrl(TenantPortal::origin($request));
    }
}
