<?php

namespace App\Http\Controllers\Central;

use App\Enums\PlatformRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\PlatformAnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private PlatformAnalyticsService $platformAnalyticsService,
    ) {}

    public function __invoke(): View
    {
        $user = auth()->user();
        $metrics = $this->platformAnalyticsService->dashboardForUser($user);

        $organizations = $user->platform_role === PlatformRole::SuperAdmin
            ? Organization::withCount('branches')->latest()->get()
            : $user->organizations()->withCount('branches')->get();

        $branches = $user->platform_role === PlatformRole::SuperAdmin
            ? Tenant::with(['organization', 'domains'])->latest()->get()
            : Tenant::with(['organization', 'domains'])
                ->whereIn('organization_id', $organizations->pluck('id'))
                ->latest()
                ->get();

        return view('central.dashboard', array_merge($metrics, [
            'organizations' => $organizations,
            'branches' => $branches,
        ]));
    }
}
