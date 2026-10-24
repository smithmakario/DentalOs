<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleAuthService $googleAuth) {}

    public function redirectPlatform(Request $request): SymfonyRedirectResponse
    {
        $this->googleAuth->rememberContext($request, GoogleAuthService::CONTEXT_PLATFORM);

        return Socialite::driver('google')->redirect();
    }

    public function redirectClinic(Request $request): SymfonyRedirectResponse
    {
        $this->googleAuth->rememberContext($request, GoogleAuthService::CONTEXT_CLINIC);

        return Socialite::driver('google')->redirect();
    }

    public function redirectTenant(Request $request): SymfonyRedirectResponse
    {
        $tenant = tenant();

        abort_unless($tenant instanceof Tenant, 404);

        $this->googleAuth->rememberContext($request, GoogleAuthService::CONTEXT_TENANT, $tenant);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $failureUrl = $this->googleAuth->loginFailureUrl($request);

        if ($request->filled('error')) {
            $this->googleAuth->clearContext($request);

            return redirect()
                ->to($failureUrl)
                ->withErrors(['email' => __('Google sign-in was cancelled.')]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            return $this->googleAuth->complete($request, $googleUser);
        } catch (ValidationException $exception) {
            $this->googleAuth->clearContext($request);

            return redirect()
                ->to($failureUrl)
                ->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::warning('Google OAuth callback failed.', [
                'message' => $exception->getMessage(),
            ]);

            $this->googleAuth->clearContext($request);

            return redirect()
                ->to($failureUrl)
                ->withErrors(['email' => __('Google sign-in failed. Please try again or use your password.')]);
        }
    }
}
