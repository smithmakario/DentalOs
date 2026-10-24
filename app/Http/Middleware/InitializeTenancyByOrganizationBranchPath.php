<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\Tenant;
use App\Support\TenantPortal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InitializeTenancyByOrganizationBranchPath
{
    public function __construct(private Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Route|null $route */
        $route = $request->route();

        if ($route === null) {
            abort(404);
        }

        $organizationSlug = $route->parameter('organization');
        $branchSlug = $route->parameter('branch');

        if (! is_string($organizationSlug) || ! is_string($branchSlug)) {
            throw new NotFoundHttpException(__('This branch could not be found.'));
        }

        $organization = Organization::query()
            ->where('slug', $organizationSlug)
            ->first();

        if ($organization === null) {
            throw new NotFoundHttpException(__('This clinic could not be found.'));
        }

        $isMediaRequest = is_string($route->getName())
            && str_ends_with($route->getName(), '.media');

        // Branding assets stay reachable even before a subscription is assigned.
        if (! $organization->is_active && ! $isMediaRequest) {
            throw new NotFoundHttpException(__('This clinic is not active yet. A subscription plan must be assigned before staff can sign in.'));
        }

        $tenant = Tenant::query()
            ->where('organization_id', $organization->id)
            ->where('slug', $branchSlug)
            ->first();

        if ($tenant === null) {
            throw new NotFoundHttpException(__('This branch could not be found.'));
        }

        if (! $tenant->is_active && ! $isMediaRequest) {
            throw new NotFoundHttpException(__('This branch is not active yet.'));
        }

        $route->forgetParameter('organization');
        $route->forgetParameter('branch');

        $this->tenancy->initialize($tenant);

        TenantPortal::applyUrlRootForCurrentTenant($request);

        return $next($request);
    }
}
