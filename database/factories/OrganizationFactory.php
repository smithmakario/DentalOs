<?php

namespace Database\Factories;

use App\Enums\OrganizationType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->company().' Dental';

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'type' => OrganizationType::Single,
            'email' => fake()->companyEmail(),
            'is_active' => true,
        ];
    }
}
