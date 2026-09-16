<?php

namespace Database\Factories;

use App\Enums\InventoryItemType;
use App\Models\InventoryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_category_id' => InventoryCategory::factory(),
            'name' => $this->faker->words(3, true),
            'type' => InventoryItemType::Consumable,
            'unit_measure' => 'piece',
            'min_stock_level' => 10,
            'is_active' => true,
        ];
    }
}
