<?php

namespace App\Http\Middleware;

use App\Support\TenantPortal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Long-running PHP servers keep URL::forceRootUrl across requests.
 * Reset to the request origin, then re-apply the path-tenant root when
 * tenancy was already initialized earlier in the stack.
 */
class ResetRequestUrlRoot
{
    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->getHost())) {
            URL::forceRootUrl(rtrim($request->getSchemeAndHttpHost(), '/'));
        }

        // Path tenancy middleware may run before the web group; do not leave
        // route() generating /patients instead of /{org}/{branch}/patients.
        if (tenancy()->initialized) {
            TenantPortal::applyUrlRootForCurrentTenant($request);
        }

        return $next($request);
    }
}
