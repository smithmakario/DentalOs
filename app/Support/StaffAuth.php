<?php

namespace App\Support;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffAuth
{
    public const SESSION_EMAIL = 'staff_auth_email';

    public static function remember(Request $request, Staff|string|null $staffOrEmail): void
    {
        $email = $staffOrEmail instanceof Staff
            ? $staffOrEmail->email
            : $staffOrEmail;

        if (! is_string($email) || $email === '') {
            return;
        }

        $request->session()->put(self::SESSION_EMAIL, strtolower($email));
    }

    public static function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_EMAIL);
    }

    public static function email(?Request $request = null): ?string
    {
        $request ??= app()->bound('request') ? request() : null;

        if (! $request instanceof Request) {
            return null;
        }

        $email = $request->session()->get(self::SESSION_EMAIL);

        return is_string($email) && $email !== '' ? strtolower($email) : null;
    }

    /**
     * After switching tenant DBs, re-bind the session staff row by email.
     */
    public static function reconcileForCurrentTenant(?Request $request = null): void
    {
        $request ??= app()->bound('request') ? request() : null;

        if (! $request instanceof Request || ! tenancy()->initialized) {
            return;
        }

        $email = self::email($request);

        if ($email === null) {
            return;
        }

        $current = Auth::guard('staff')->user();

        // Always resolve against the current tenant DB — staff ids are not shared across branches.
        $staff = Staff::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->where('is_active', true)
            ->first();

        if (! $staff instanceof Staff) {
            if ($current instanceof Staff) {
                Auth::guard('staff')->logout();
            }

            return;
        }

        if (! $current instanceof Staff || (int) $current->getKey() !== (int) $staff->getKey()) {
            Auth::guard('staff')->login($staff);
        }
    }
}
