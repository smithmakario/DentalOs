<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffLoginRequest extends FormRequest
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
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::guard('staff')->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $staff = Auth::guard('staff')->user();

        if (! $staff->is_active) {
            Auth::guard('staff')->logout();

            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        if ($staff->organization_staff_id !== null) {
            $staffMember = app(\App\Services\StaffAccessService::class)->findMemberForTenantStaff(
                $staff->organization_staff_id,
                $staff->email,
            );

            if ($staffMember === null || ! app(\App\Services\StaffAccessService::class)->canAccessTenant($staffMember, (string) tenant('id'))) {
                Auth::guard('staff')->logout();

                throw ValidationException::withMessages([
                    'email' => __('You do not have access to this branch.'),
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
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
        return Str::transliterate(Str::lower($this->string('email')).'|staff|'.$this->ip());
    }
}
