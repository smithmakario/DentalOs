<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreStaffRequest;
use App\Http\Requests\Tenant\UpdateStaffRequest;
use App\Models\Staff;
use App\Models\Tenant;
use App\Services\ClinicAdminAccess;
use App\Services\ClinicPrimaryAdminService;
use App\Support\TenantPortal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(
        private ClinicAdminAccess $clinicAdminAccess,
        private ClinicPrimaryAdminService $clinicPrimaryAdminService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Staff::class);

        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->trim()->toString();

        $staffMembers = Staff::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn (Builder $query) => $query->where('role', $role))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('tenant.staff.index', [
            'staffMembers' => $staffMembers,
            'search' => $search,
            'role' => $role,
            'roles' => StaffRole::cases(),
            'totalStaff' => Staff::count(),
            'activeStaff' => Staff::where('is_active', true)->count(),
            'providerCount' => Staff::providers()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Staff::class);

        return view('tenant.staff.create', [
            'member' => new Staff([
                'is_active' => true,
                'role' => StaffRole::Receptionist,
            ]),
            'roles' => StaffRole::branchAssignable($this->clinicAdminAccess->isAdminBranch()),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $this->authorize('create', Staff::class);

        // Capture before any cross-branch sync can switch tenancy context.
        $branch = tenant();
        $plainPassword = $request->string('password')->toString();

        $member = Staff::query()->create([
            ...$this->staffAttributes($request),
            'password' => $plainPassword,
            'email_verified_at' => now(),
        ]);

        if ($request->hasFile('avatar')) {
            $member->update([
                'avatar_path' => $this->storeAvatar($request->file('avatar'), $member),
            ]);
        }

        $this->syncClinicPrimaryIfNeeded($member, $plainPassword);

        return $this->redirectToStaffShow($member, $branch instanceof Tenant ? $branch : null)
            ->with('success', __('Staff member created successfully.'));
    }

    public function show(Staff $staff): View
    {
        abort_unless(tenancy()->initialized, 404);

        $this->authorize('view', $staff);

        $staff->loadCount(['appointmentsAsProvider', 'treatmentPlans']);

        return view('tenant.staff.show', [
            'member' => $staff,
        ]);
    }

    public function edit(Staff $staff): View
    {
        $this->authorize('update', $staff);

        return view('tenant.staff.edit', [
            'member' => $staff,
            'roles' => StaffRole::assignableFor(
                $staff->role,
                $this->clinicAdminAccess->isAdminBranch(),
            ),
        ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);

        $attributes = $this->staffAttributes($request);
        $plainPassword = $request->filled('password')
            ? $request->string('password')->toString()
            : null;

        if ($plainPassword !== null) {
            $attributes['password'] = $plainPassword;
        }

        if ($request->boolean('remove_avatar') && $staff->avatar_path) {
            Storage::disk('public')->delete($staff->avatar_path);
            $attributes['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($staff->avatar_path) {
                Storage::disk('public')->delete($staff->avatar_path);
            }

            $attributes['avatar_path'] = $this->storeAvatar($request->file('avatar'), $staff);
        }

        if ($staff->id === $request->user('staff')?->id) {
            $attributes['is_active'] = true;
        }

        $branch = tenant();

        $staff->update($attributes);

        $this->syncClinicPrimaryIfNeeded($staff->fresh(), $plainPassword);

        return $this->redirectToStaffShow($staff, $branch instanceof Tenant ? $branch : null)
            ->with('success', __('Staff member updated successfully.'));
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);

        $staff->update(['is_active' => false]);

        return redirect()
            ->route('tenant.staff.index')
            ->with('success', __('Staff member deactivated successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function staffAttributes(StoreStaffRequest|UpdateStaffRequest $request): array
    {
        return [
            'first_name' => $request->string('first_name')->toString(),
            'last_name' => $request->string('last_name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->trim()->toString() ?: null,
            'role' => $request->enum('role', StaffRole::class),
            'specialization' => $request->string('specialization')->trim()->toString() ?: null,
            'license_number' => $request->string('license_number')->trim()->toString() ?: null,
            'years_of_experience' => $request->filled('years_of_experience')
                ? $request->integer('years_of_experience')
                : null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function storeAvatar(UploadedFile $file, Staff $member): string
    {
        return $file->store("staff-avatars/{$member->id}", 'public');
    }

    private function syncClinicPrimaryIfNeeded(Staff $member, ?string $plainPassword): void
    {
        if ($member->role !== StaffRole::ClinicAdmin) {
            return;
        }

        if (! $this->clinicAdminAccess->isAdminBranch()) {
            return;
        }

        $organization = $this->clinicAdminAccess->organization();

        if ($organization === null) {
            return;
        }

        $this->clinicPrimaryAdminService->promoteStaffToClinicPrimary(
            $organization,
            $member,
            $plainPassword,
        );
    }

    private function redirectToStaffShow(Staff $member, ?Tenant $branch = null): RedirectResponse
    {
        $tenant = $branch ?? tenant();

        if ($tenant instanceof Tenant) {
            if (tenancy()->initialized
                && (string) tenant('id') !== (string) $tenant->getTenantKey()) {
                tenancy()->initialize($tenant);
            }

            TenantPortal::applyUrlRootForCurrentTenant(request());

            // Prefer absolute path URLs so cross-branch sync cannot leave
            // route() generating a central /staff/{id} link without tenancy.
            return redirect()->to(TenantPortal::absolute($tenant, '/staff/'.$member->id));
        }

        return redirect()->route('tenant.staff.show', $member);
    }
}
