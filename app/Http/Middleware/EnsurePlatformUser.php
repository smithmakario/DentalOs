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

        auth()->logout();

        if ($user->platform_role === PlatformRole::OrgAdmin) {
            return redirect()
                ->route('clinic.login')
                ->withErrors(['email' => __('Primary clinic admins sign in at the clinic portal.')]);
        }

        return redirect()
            ->route('login')
            ->withErrors(['email' => __('You do not have platform access.')]);
    }
}
