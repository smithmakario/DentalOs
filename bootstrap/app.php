<?php

use App\Http\Middleware\EnsureCentralDomain;
use App\Http\Middleware\EnsureClinicPortalAccess;
use App\Http\Middleware\EnsurePlatformUser;
use App\Http\Middleware\EnsureStaffBranchAccess;
use App\Http\Middleware\EnsureStaffUser;
use App\Http\Middleware\ResetRequestUrlRoot;
use App\Support\AuthRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\TenantCannotBeCreatedException;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'central' => EnsureCentralDomain::class,
            'clinic' => EnsureClinicPortalAccess::class,
            'platform' => EnsurePlatformUser::class,
            'staff' => EnsureStaffUser::class,
            'branch.staff' => EnsureStaffBranchAccess::class,
        ]);

        // php artisan serve / Octane keep forceRootUrl across requests; reset every hit.
        $middleware->prependToGroup('web', ResetRequestUrlRoot::class);

        // Staff/clinic sessions must not bounce to the platform /login or /dashboard.
        $middleware->redirectGuestsTo(fn (Request $request): string => AuthRedirect::forGuests($request));
        $middleware->redirectUsersTo(fn (Request $request): string => AuthRedirect::forUsers($request));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            TenantCouldNotBeIdentifiedException::class,
        ]);

        $exceptions->render(function (TenantCouldNotBeIdentifiedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('This clinic or branch could not be found.'),
                ], 404);
            }

            return response()->view('errors.404', [
                'exception' => new NotFoundHttpException(
                    __('This clinic or branch could not be found.'),
                ),
            ], 404);
        });

        $exceptions->render(function (TenantCannotBeCreatedException $e, Request $request) {
            $message = __('This branch could not be provisioned because its database already exists. Try again, or run migrate:fresh once more so leftover tenant databases are cleaned up.');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['branch_slug' => $message]);
        });
    })->create();
