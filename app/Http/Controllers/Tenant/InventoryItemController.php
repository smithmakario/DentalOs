<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\InventoryItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInventoryItemRequest;
use App\Http\Requests\Tenant\UpdateInventoryItemRequest;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $filterType = $request->string('filter')->trim()->toString();

        $items = InventoryItem::query()
            ->with(['category', 'activeBatches'])
            ->withSum('activeBatches as total_stock', 'current_quantity')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($filterType === 'low_stock', fn ($q) => $q->lowStock())
            ->when($filterType === 'out_of_stock', fn ($q) => $q->outOfStock())
            ->when($filterType === 'expiring_soon', fn ($q) => $q->withExpiringBatches())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $lowStockCount = InventoryItem::lowStock()->count();
        $outOfStockCount = InventoryItem::outOfStock()->count();
        $expiringSoonCount = InventoryItem::withExpiringBatches()->count();

        return view('tenant.inventory.index', [
            'items' => $items,
            'search' => $search,
            'filterType' => $filterType,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'expiringSoonCount' => $expiringSoonCount,
        ]);
    }

    public function create(): View
    {
        return view('tenant.inventory.create', [
            'categories' => InventoryCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'types' => InventoryItemType::cases(),
            'item' => new InventoryItem(['is_active' => true]),
        ]);
    }

    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        $item = InventoryItem::create($request->validated());

        return redirect()
            ->route('tenant.inventory.show', $item)
            ->with('success', __('Inventory item created successfully.'));
    }

    public function show(InventoryItem $inventory): View
    {
        $inventory->load([
            'category',
            'batches' => fn ($q) => $q->orderBy('expiry_date'),
            'transactions' => fn ($q) => $q->with(['batch', 'staffMember'])->latest()->limit(20),
        ]);

        return view('tenant.inventory.show', [
            'item' => $inventory,
        ]);
    }

    public function edit(InventoryItem $inventory): View
    {
        return view('tenant.inventory.edit', [
            'item' => $inventory,
            'categories' => InventoryCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'types' => InventoryItemType::cases(),
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventory): RedirectResponse
    {
        $inventory->update($request->validated());

        return redirect()
            ->route('tenant.inventory.show', $inventory)
            ->with('success', __('Inventory item updated successfully.'));
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $inventory->update(['is_active' => false]);

        return redirect()
            ->route('tenant.inventory.index')
            ->with('success', __('Inventory item deactivated.'));
    }
}
