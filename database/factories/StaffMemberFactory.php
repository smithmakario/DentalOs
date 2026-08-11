<?php

namespace Database\Factories;

use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\StaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffMember>
 */
class StaffMemberFactory extends Factory
{
    protected $model = StaffMember::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => 'password',
            'role' => StaffRole::Dentist,
            'has_global_branch_access' => false,
            'is_active' => true,
        ];
    }

    public function globalAccess(): static
    {
        return $this->state(fn (): array => [
            'has_global_branch_access' => true,
        ]);
    }
}
