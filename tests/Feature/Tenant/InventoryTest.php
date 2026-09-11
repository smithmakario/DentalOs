<?php

namespace Tests\Feature\Tenant;

use App\Enums\InventoryItemType;
use App\Enums\InventoryTransactionType;
use App\Enums\StaffRole;
use App\Models\InventoryBatch;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Tests\TenantTestCase;

class InventoryTest extends TenantTestCase
{
    private function makeCategory(): InventoryCategory
    {
        return $this->tenant->run(fn () => InventoryCategory::factory()->create(['is_active' => true]));
    }

    private function makeItem(InventoryCategory $category): InventoryItem
    {
        return $this->tenant->run(fn () => InventoryItem::factory()->create([
            'inventory_category_id' => $category->id,
            'type' => InventoryItemType::Medication,
            'unit_measure' => 'tablet',
            'min_stock_level' => 10,
            'is_active' => true,
        ]));
    }

    // ---------- Index ----------

    public function test_staff_can_view_inventory_index(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $category = $this->makeCategory();
        $this->makeItem($category);

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/inventory'));

        $response->assertOk()->assertViewIs('tenant.inventory.index');
    }

    // ---------- Create / Store ----------

    public function test_staff_can_create_an_inventory_item(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $category = $this->makeCategory();

        $response = $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl('/inventory'), [
                'inventory_category_id' => $category->id,
                'name' => 'Ibuprofen 400mg',
                'type' => InventoryItemType::Medication->value,
                'unit_measure' => 'tablet',
                'min_stock_level' => 50,
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->tenant->run(function (): void {
            $this->assertDatabaseHas('inventory_items', ['name' => 'Ibuprofen 400mg']);
        });
    }

    // ---------- Batch / Receive Stock ----------

    public function test_staff_can_add_a_batch_to_an_item(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $category = $this->makeCategory();
        $item = $this->makeItem($category);

        $response = $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/inventory/{$item->id}/batches"), [
                'lot_number' => 'LOT-2024-001',
                'expiry_date' => now()->addYear()->format('Y-m-d'),
                'initial_quantity' => 100,
            ]);

        $response->assertRedirect($this->tenantUrl("/inventory/{$item->id}"));

        $this->tenant->run(function () use ($item): void {
            $this->assertDatabaseHas('inventory_batches', [
                'inventory_item_id' => $item->id,
                'lot_number' => 'LOT-2024-001',
                'initial_quantity' => 100,
                'current_quantity' => 100,
            ]);
            $this->assertDatabaseHas('inventory_transactions', [
                'inventory_item_id' => $item->id,
                'type' => InventoryTransactionType::Restock->value,
                'quantity' => 100,
            ]);
        });
    }

    // ---------- Consume Transaction ----------

    public function test_consuming_reduces_batch_quantity(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $category = $this->makeCategory();
        $item = $this->makeItem($category);

        /** @var InventoryBatch $batch */
        $batch = $this->tenant->run(fn () => InventoryBatch::factory()->create([
            'inventory_item_id' => $item->id,
            'initial_quantity' => 100,
            'current_quantity' => 100,
            'is_active' => true,
        ]));

        $response = $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/inventory/{$item->id}/transactions"), [
                'inventory_batch_id' => $batch->id,
                'type' => InventoryTransactionType::Consume->value,
                'quantity' => 30,
            ]);

        $response->assertRedirect($this->tenantUrl("/inventory/{$item->id}"));

        $this->tenant->run(function () use ($batch): void {
            $this->assertDatabaseHas('inventory_batches', [
                'id' => $batch->id,
                'current_quantity' => 70, // 100 - 30
            ]);
        });
    }

    // ---------- Adjustment Transaction ----------

    public function test_adjustment_sets_batch_to_exact_quantity(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $category = $this->makeCategory();
        $item = $this->makeItem($category);

        /** @var InventoryBatch $batch */
        $batch = $this->tenant->run(fn () => InventoryBatch::factory()->create([
            'inventory_item_id' => $item->id,
            'initial_quantity' => 100,
            'current_quantity' => 60,
            'is_active' => true,
        ]));

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/inventory/{$item->id}/transactions"), [
                'inventory_batch_id' => $batch->id,
                'type' => InventoryTransactionType::Adjustment->value,
                'quantity' => 45,
            ]);

        $this->tenant->run(function () use ($batch): void {
            $this->assertDatabaseHas('inventory_batches', [
                'id' => $batch->id,
                'current_quantity' => 45,
            ]);
        });
    }

    // ---------- Low Stock Scope ----------

    public function test_low_stock_scope_identifies_items_below_minimum(): void
    {
        $category = $this->makeCategory();
        $item = $this->makeItem($category); // min_stock_level = 10

        $this->tenant->run(function () use ($item): void {
            InventoryBatch::factory()->create([
                'inventory_item_id' => $item->id,
                'initial_quantity' => 5,
                'current_quantity' => 5, // below min of 10
                'is_active' => true,
            ]);

            $lowStock = InventoryItem::lowStock()->get();
            $this->assertGreaterThan(0, $lowStock->count());
            $this->assertTrue($lowStock->contains('id', $item->id));
        });
    }

    // ---------- Out of Stock Scope ----------

    public function test_out_of_stock_scope_identifies_items_with_no_active_batches(): void
    {
        $category = $this->makeCategory();
        $item = $this->makeItem($category);

        // No batches created — item has zero stock.
        $this->tenant->run(function () use ($item): void {
            $outOfStock = InventoryItem::outOfStock()->get();
            $this->assertTrue($outOfStock->contains('id', $item->id));
        });
    }

    // ---------- Expiring Soon Scope ----------

    public function test_expiring_soon_scope_detects_batches_within_30_days(): void
    {
        $category = $this->makeCategory();
        $item = $this->makeItem($category);

        $this->tenant->run(function () use ($item): void {
            InventoryBatch::factory()->create([
                'inventory_item_id' => $item->id,
                'initial_quantity' => 20,
                'current_quantity' => 20,
                'expiry_date' => now()->addDays(15),
                'is_active' => true,
            ]);

            $expiring = InventoryItem::withExpiringBatches()->get();
            $this->assertTrue($expiring->contains('id', $item->id));
        });
    }

    // ---------- Edit / Update ----------

    public function test_staff_can_update_inventory_item(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $category = $this->makeCategory();
        $item = $this->makeItem($category);

        $this->actingAs($staff, 'staff')
            ->patch($this->tenantUrl("/inventory/{$item->id}"), [
                'inventory_category_id' => $category->id,
                'name' => 'Updated Name',
                'type' => InventoryItemType::Consumable->value,
                'unit_measure' => 'box',
                'min_stock_level' => 5,
                'is_active' => true,
            ]);

        $this->tenant->run(function () use ($item): void {
            $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'name' => 'Updated Name']);
        });
    }
}
