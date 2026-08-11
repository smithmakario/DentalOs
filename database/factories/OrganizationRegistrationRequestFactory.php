<?php

namespace Database\Factories;

use App\Enums\RegistrationRequestStatus;
use App\Models\OrganizationRegistrationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrganizationRegistrationRequest>
 */
class OrganizationRegistrationRequestFactory extends Factory
{
    protected $model = OrganizationRegistrationRequest::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Dental',
            'location' => fake()->address(),
            'government_approval' => 'LIC-'.fake()->unique()->numerify('######'),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => RegistrationRequestStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => RegistrationRequestStatus::Approved,
            'onboarding_token' => Str::random(64),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (): array => [
            'status' => RegistrationRequestStatus::Rejected,
            'rejection_reason' => $reason ?? fake()->sentence(),
            'reviewed_at' => now(),
        ]);
    }
}
