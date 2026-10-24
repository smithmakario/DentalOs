<?php

namespace App\Services;

use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ClinicPortal;
use App\Support\StaffAuth;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public const CONTEXT_PLATFORM = 'platform';

    public const CONTEXT_CLINIC = 'clinic';

    public const CONTEXT_TENANT = 'tenant';

    public function __construct(
        private readonly ClinicAdminAccess $clinicAdminAccess,
        private readonly ClinicSubscriptionAccess $subscriptionAccess,
        private readonly StaffAccessService $staffAccess,
        private readonly PrimaryClinicAdminProvisioner $primaryAdminProvisioner,
    ) {}

    public function rememberContext(Request $request, string $context, ?Tenant $tenant = null): void
    {
        $request->session()->put('google_oauth_context', $context);
        $request->session()->put('google_oauth_tenant_id', $tenant?->getTenantKey());
        $request->session()->put(
            'google_oauth_return',
            match ($context) {
                self::CONTEXT_CLINIC => route('clinic.login'),
                self::CONTEXT_TENANT => $tenant instanceof Tenant
                    ? TenantPortal::absolute($tenant, '/staff/login', $request)
                    : route('login'),
                default => route('login'),
            },
        );
    }

    public function clearContext(Request $request): void
    {
        $request->session()->forget([
            'google_oauth_context',
            'google_oauth_tenant_id',
            'google_oauth_return',
        ]);
    }

    public function loginFailureUrl(Request $request): string
    {
        return (string) $request->session()->get('google_oauth_return', route('login'));
    }

    /**
     * @throws ValidationException
     */
    public function complete(Request $request, SocialiteUser $googleUser): RedirectResponse
    {
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();

        if ($email === '' || $googleId === '') {
            throw ValidationException::withMessages([
                'email' => __('Google did not return a valid email address for this account.'),
            ]);
        }

        $context = (string) $request->session()->get('google_oauth_context', self::CONTEXT_PLATFORM);

        return match ($context) {
            self::CONTEXT_CLINIC => $this->loginClinic($request, $email, $googleId),
            self::CONTEXT_TENANT => $this->loginTenant($request, $email, $googleId),
            default => $this->loginPlatform($request, $email, $googleId),
        };
    }

    /**
     * @throws ValidationException
     */
    protected function loginPlatform(Request $request, string $email, string $googleId): RedirectResponse
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $user = User::query()
            ->where(function ($query) use ($email, $googleId): void {
                $query->where('email', $email)
                    ->orWhere('google_id', $googleId);
            })
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => __('No platform account matches this Google email. Ask an administrator to create your account first.'),
            ]);
        }

        if ($user->platform_role === PlatformRole::OrgAdmin) {
            throw ValidationException::withMessages([
                'email' => __('Primary clinic admins must sign in at the clinic portal (/clinic/login), not here.'),
            ]);
        }

        if ($user->platform_role !== PlatformRole::SuperAdmin) {
            throw ValidationException::withMessages([
                'email' => __('You do not have platform access.'),
            ]);
        }

        $this->linkGoogleId($user, $googleId);

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        $this->clearContext($request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * @throws ValidationException
     */
    protected function loginClinic(Request $request, string $email, string $googleId): RedirectResponse
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $user = User::query()
            ->where('email', $email)
            ->where('platform_role', PlatformRole::OrgAdmin)
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => __('No clinic admin account matches this Google email.'),
            ]);
        }

        $organization = $user->organizations()
            ->whereNotNull('admin_branch_id')
            ->orderBy('organizations.id')
            ->first();

        if ($organization === null || $organization->adminBranch === null) {
            throw ValidationException::withMessages([
                'email' => __('No clinic admin branch is available for this account.'),
            ]);
        }

        if (! $this->subscriptionAccess->allowsStaffAccess($organization)) {
            throw ValidationException::withMessages([
                'email' => $this->subscriptionAccess->denialReason($organization),
            ]);
        }

        tenancy()->initialize($organization->adminBranch);

        $staff = Staff::query()->where('email', $email)->first();

        if ($staff === null || ! $staff->is_active || ! $this->clinicAdminAccess->canManageClinic($staff, $organization->adminBranch)) {
            tenancy()->end();

            throw ValidationException::withMessages([
                'email' => __('Only the primary clinic admin can sign in here.'),
            ]);
        }

        $this->linkGoogleId($user, $googleId);
        $this->linkGoogleId($staff, $googleId);

        Auth::guard('staff')->login($staff, true);
        $request->session()->regenerate();
        ClinicPortal::mark($request, $organization);
        StaffAuth::remember($request, $staff);
        $this->primaryAdminProvisioner->syncToAllBranches($organization);
        $this->clearContext($request);

        return redirect()->to(TenantPortal::url($organization->adminBranch, '/dashboard', $request));
    }

    /**
     * @throws ValidationException
     */
    protected function loginTenant(Request $request, string $email, string $googleId): RedirectResponse
    {
        $tenantId = $request->session()->get('google_oauth_tenant_id');
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant instanceof Tenant) {
            throw ValidationException::withMessages([
                'email' => __('This clinic branch could not be found for Google sign-in.'),
            ]);
        }

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        tenancy()->initialize($tenant);

        $staff = Staff::query()->where('email', $email)->first();

        if ($staff === null || ! $staff->is_active) {
            tenancy()->end();

            throw ValidationException::withMessages([
                'email' => __('No active staff account matches this Google email for this branch.'),
            ]);
        }

        $organization = $tenant->organization;

        if ($organization instanceof Organization && ! $this->subscriptionAccess->allowsStaffAccess($organization)) {
            tenancy()->end();

            throw ValidationException::withMessages([
                'email' => $this->subscriptionAccess->denialReason($organization),
            ]);
        }

        if ($staff->organization_staff_id !== null) {
            $staffMember = $this->staffAccess->findMemberForTenantStaff(
                $staff->organization_staff_id,
                $staff->email,
            );

            if ($staffMember === null || ! $this->staffAccess->canAccessTenant($staffMember, (string) $tenant->getTenantKey())) {
                tenancy()->end();

                throw ValidationException::withMessages([
                    'email' => __('You do not have access to this branch.'),
                ]);
            }
        }

        $this->linkGoogleId($staff, $googleId);

        Auth::guard('staff')->login($staff, true);
        $request->session()->regenerate();
        StaffAuth::remember($request, $staff);

        if ($this->clinicAdminAccess->canManageClinic($staff, $tenant) && $organization instanceof Organization) {
            ClinicPortal::mark($request, $organization);
            $this->primaryAdminProvisioner->syncToAllBranches($organization);
        }

        $this->clearContext($request);

        return redirect()->intended(TenantPortal::absolute($tenant, '/dashboard', $request));
    }

    protected function linkGoogleId(User|Staff $account, string $googleId): void
    {
        if ($account->google_id === $googleId) {
            return;
        }

        $account->forceFill(['google_id' => $googleId])->save();
    }
}
