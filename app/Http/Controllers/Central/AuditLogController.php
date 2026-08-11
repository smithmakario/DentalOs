<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Enums\PlatformRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $user = $request->user();
        $action = $request->string('action')->trim()->toString();
        $organizationId = $request->integer('organization_id');

        $logsQuery = AuditLog::query()
            ->with(['user', 'organization'])
            ->when($user->platform_role !== PlatformRole::SuperAdmin, function (Builder $query) use ($user): void {
                $query->whereIn('organization_id', $user->organizations()->pluck('organizations.id'));
            })
            ->when($action !== '', fn (Builder $query) => $query->where('action', $action))
            ->when($organizationId > 0, fn (Builder $query) => $query->where('organization_id', $organizationId))
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest('created_at');

        $totalLogs = (clone $logsQuery)->count();

        $organizations = $user->platform_role === PlatformRole::SuperAdmin
            ? Organization::query()->orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['organizations.id', 'organizations.name']);

        return view('central.audit-logs.index', [
            'logs' => $logsQuery->paginate(20)->withQueryString(),
            'organizations' => $organizations,
            'actions' => AuditAction::cases(),
            'filters' => [
                'action' => $action,
                'organization_id' => $organizationId,
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ],
            'totalLogs' => $totalLogs,
        ]);
    }
}
