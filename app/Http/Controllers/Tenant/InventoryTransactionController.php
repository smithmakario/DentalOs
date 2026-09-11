<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\InventoryTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInventoryBatchRequest;
use App\Http\Requests\Tenant\StoreInventoryTransactionRequest;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\RedirectResponse;

class InventoryTransactionController extends Controller
{
    /**
     * Record a new batch/lot for an inventory item (restocking a new delivery).
     */
    public function storeBatch(StoreInventoryBatchRequest $request, InventoryItem $inventory): RedirectResponse
    {
        $validated = $request->validated();

        $batch = $inventory->batches()->create([
            'lot_number' => $validated['lot_number'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'initial_quantity' => $validated['initial_quantity'],
            'current_quantity' => $validated['initial_quantity'],
            'is_active' => true,
        ]);

        // Record a Restock transaction for audit trail.
        InventoryTransaction::create([
            'inventory_item_id' => $inventory->id,
            'inventory_batch_id' => $batch->id,
            'type' => InventoryTransactionType::Restock,
            'quantity' => $validated['initial_quantity'],
            'notes' => 'New batch received. Lot: '.($batch->lot_number ?? 'N/A'),
            'staff_member_id' => auth()->id(),
        ]);

        return redirect()
            ->route('tenant.inventory.show', $inventory)
            ->with('success', __('Batch added successfully.'));
    }

    /**
     * Record a stock movement (consume, adjust, mark expired/damaged) against a specific batch.
     */
    public function store(StoreInventoryTransactionRequest $request, InventoryItem $inventory): RedirectResponse
    {
        $validated = $request->validated();
        $type = InventoryTransactionType::from($validated['type']);

        /** @var InventoryBatch|null $batch */
        $batch = isset($validated['inventory_batch_id'])
            ? $inventory->batches()->findOrFail($validated['inventory_batch_id'])
            : null;

        // Deducting transaction types reduce batch stock.
        $isDeduction = in_array($type, [
            InventoryTransactionType::Consume,
            InventoryTransactionType::Expired,
            InventoryTransactionType::Damaged,
        ]);

        if ($batch && $isDeduction) {
            $newQty = max(0, (float) $batch->current_quantity - (float) $validated['quantity']);
            $batch->update([
                'current_quantity' => $newQty,
                'is_active' => $newQty > 0,
            ]);
        }

        if ($batch && $type === InventoryTransactionType::Adjustment) {
            $batch->update(['current_quantity' => $validated['quantity']]);
        }

        InventoryTransaction::create([
            'inventory_item_id' => $inventory->id,
            'inventory_batch_id' => $batch?->id,
            'type' => $type,
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? null,
            'staff_member_id' => auth()->id(),
        ]);

        return redirect()
            ->route('tenant.inventory.show', $inventory)
            ->with('success', __('Stock record updated successfully.'));
    }
}
