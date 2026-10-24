<?php

namespace App\Http\Requests\Clinic;

use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\User;
use App\Services\ClinicAdminAccess;
use App\Services\ClinicSubscriptionAccess;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClinicLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): Organization
    {
        $this->ensureIsNotRateLimited();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $user = User::query()
            ->where('email', $this->string('email')->toString())
            ->where('platform_role', PlatformRole::OrgAdmin)
            ->first();

        if ($user === null || ! Hash::check($this->string('password')->toString(), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $organization = $user->organizations()
            ->whereNotNull('admin_branch_id')
            ->orderBy('organizations.id')
            ->first();

        if ($organization === null || $organization->adminBranch === null) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('No clinic admin branch is available for this account.'),
            ]);
        }

        $subscriptionAccess = app(ClinicSubscriptionAccess::class);

        if (! $subscriptionAccess->allowsStaffAccess($organization)) {
            throw ValidationException::withMessages([
                'email' => $subscriptionAccess->denialReason($organization),
            ]);
        }

        tenancy()->initialize($organization->adminBranch);

        if (! Auth::guard('staff')->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            tenancy()->end();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $staff = Auth::guard('staff')->user();

        if ($staff === null || ! $staff->is_active || ! app(ClinicAdminAccess::class)->canManageClinic($staff, $organization->adminBranch)) {
            Auth::guard('staff')->logout();
            tenancy()->end();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('Only the primary clinic admin can sign in here.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $organization;
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|clinic|'.$this->ip());
    }
}
