<?php

namespace Database\Factories;

use App\Models\BranchProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchProfile>
 */
class BranchProfileFactory extends Factory
{
    protected $model = BranchProfile::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'branch_prefix' => strtoupper(fake()->lexify('??')),
            'contact_email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'Nigeria',
            'timezone' => 'Africa/Lagos',
        ];
    }
}
