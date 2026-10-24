<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TenantPortal
{
    public static function pathPrefix(Tenant $tenant): string
    {
        $tenant->loadMissing('organization');

        return trim($tenant->organization->slug.'/'.$tenant->slug, '/');
    }

    /**
     * Prefer the current request origin (host + port) so local links stay on
     * 127.0.0.1:8001 instead of flipping to APP_URL's localhost.
     */
    public static function origin(?Request $request = null): string
    {
        $request ??= app()->bound('request') ? request() : null;

        if ($request instanceof Request && filled($request->getHost())) {
            return rtrim($request->getSchemeAndHttpHost(), '/');
        }

        return rtrim((string) config('app.url'), '/');
    }

    public static function rootUrl(Tenant $tenant, ?Request $request = null): string
    {
        return self::origin($request).'/'.self::pathPrefix($tenant);
    }

    public static function url(Tenant $tenant, string $path = '/', ?Request $request = null): string
    {
        $path = '/'.ltrim($path, '/');

        if ($path === '/') {
            return self::rootUrl($tenant, $request);
        }

        return self::rootUrl($tenant, $request).$path;
    }

    /**
     * Tenant URL matching how the current request identified the branch.
     * Path tenancy (and central/clinic entry) → /{org}/{branch}/…
     * Domain tenancy → /… on the tenant host.
     */
    public static function absolute(Tenant $tenant, string $path = '/', ?Request $request = null): string
    {
        $request ??= app()->bound('request') ? request() : null;
        $path = '/'.ltrim($path, '/');

        if (self::requestUsesPathTenancy($request) || ! self::requestIsTenantDomain($request)) {
            return self::url($tenant, $path, $request);
        }

        return rtrim(self::origin($request), '/').($path === '/' ? '' : $path);
    }

    public static function loginUrl(Tenant $tenant, ?Request $request = null): string
    {
        return self::url($tenant, '/staff/login', $request);
    }

    public static function requestIsTenantDomain(?Request $request = null): bool
    {
        $request ??= app()->bound('request') ? request() : null;

        if (! $request instanceof Request || blank($request->getHost())) {
            return false;
        }

        $centralDomains = array_map(
            static fn (mixed $domain): string => strtolower((string) $domain),
            config('tenancy.central_domains', []),
        );

        return ! in_array(strtolower($request->getHost()), $centralDomains, true);
    }

    public static function applyUrlRootForCurrentTenant(?Request $request = null): void
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $request ??= app()->bound('request') ? request() : null;

        // Path-based routes need /{org}/{branch} in the root; domain tenants do not.
        if (self::requestUsesPathTenancy($request)) {
            URL::forceRootUrl(self::rootUrl($tenant, $request));

            return;
        }

        URL::forceRootUrl(self::origin($request));
    }

    public static function requestUsesPathTenancy(?Request $request = null): bool
    {
        $request ??= app()->bound('request') ? request() : null;

        if (! $request instanceof Request) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && str_starts_with($routeName, 'path.tenant.')) {
            return true;
        }

        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return false;
        }

        $prefix = '/'.self::pathPrefix($tenant);

        return str_starts_with('/'.ltrim($request->path(), '/'), $prefix.'/')
            || '/'.ltrim($request->path(), '/') === $prefix;
    }
}
