<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthRedirect
{
    public static function forGuests(Request $request): string
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            return TenantPortal::loginUrl($tenant, $request);
        }

        if ($request->is('clinic', 'clinic/*')) {
            return route('clinic.login');
        }

        return route('login');
    }

    public static function forUsers(Request $request): string
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant && Auth::guard('staff')->check()) {
            return TenantPortal::absolute($tenant, '/dashboard', $request);
        }

        if (Auth::guard('web')->check()) {
            return route('dashboard');
        }

        if ($tenant instanceof Tenant) {
            return TenantPortal::absolute($tenant, '/dashboard', $request);
        }

        return route('dashboard');
    }
}
