<?php

namespace App\Http\Middleware;

use App\Enums\PlatformRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->platform_role === PlatformRole::SuperAdmin) {
            return $next($request);
        }

        if ($user->platform_role === PlatformRole::OrgAdmin && $user->organizations()->exists()) {
            return $next($request);
        }

        auth()->logout();

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'You do not have platform access.']);
    }
}
