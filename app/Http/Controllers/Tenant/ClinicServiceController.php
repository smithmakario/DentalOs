<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreClinicServiceRequest;
use App\Http\Requests\Tenant\UpdateClinicServiceRequest;
use App\Models\ClinicService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicServiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClinicService::class);

        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();

        $services = ClinicService::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn (Builder $query) => $query->where('category', $category))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = ClinicService::query()->distinct()->orderBy('category')->pluck('category');

        return view('tenant.clinic-services.index', [
            'services' => $services,
            'search' => $search,
            'category' => $category,
            'categories' => $categories,
            'activeCount' => ClinicService::where('is_active', true)->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ClinicService::class);

        return view('tenant.clinic-services.create', [
            'service' => new ClinicService([
                'is_active' => true,
                'is_recommended' => false,
                'price' => 0,
                'duration_minutes' => 30,
                'icon' => 'medical_services',
            ]),
        ]);
    }

    public function store(StoreClinicServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', ClinicService::class);

        ClinicService::query()->create([
            ...$request->validated(),
            'is_recommended' => $request->boolean('is_recommended', false),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('tenant.clinic-services.index')
            ->with('success', __('Service created successfully.'));
    }

    public function edit(ClinicService $clinic_service): View
    {
        $this->authorize('update', $clinic_service);

        return view('tenant.clinic-services.edit', [
            'service' => $clinic_service,
        ]);
    }

    public function update(UpdateClinicServiceRequest $request, ClinicService $clinic_service): RedirectResponse
    {
        $this->authorize('update', $clinic_service);

        $clinic_service->update([
            ...$request->validated(),
            'is_recommended' => $request->boolean('is_recommended', false),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('tenant.clinic-services.index')
            ->with('success', __('Service updated successfully.'));
    }

    public function destroy(ClinicService $clinic_service): RedirectResponse
    {
        $this->authorize('delete', $clinic_service);

        $clinic_service->delete();

        return redirect()
            ->route('tenant.clinic-services.index')
            ->with('success', __('Service deleted successfully.'));
    }
}
