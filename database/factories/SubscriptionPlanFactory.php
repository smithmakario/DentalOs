<?php

namespace Database\Factories;

use App\Enums\OrganizationType;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'organization_type' => OrganizationType::Single,
            'description' => fake()->sentence(),
            'price_monthly' => fake()->numberBetween(10000, 500000),
            'price_yearly' => fake()->numberBetween(100000, 5000000),
            'max_branches' => fake()->numberBetween(1, 10),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
