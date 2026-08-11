<?php

namespace Database\Seeders;

use App\Enums\OrganizationType;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Essential',
                'slug' => 'essential',
                'organization_type' => OrganizationType::Single,
                'description' => 'Single-location practices getting started on DentaFlow.',
                'price_monthly' => 29900,
                'price_yearly' => 299000,
                'max_branches' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'organization_type' => OrganizationType::Single,
                'description' => 'Growing clinics with multiple providers and locations.',
                'price_monthly' => 79900,
                'price_yearly' => 799000,
                'max_branches' => 5,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'organization_type' => OrganizationType::Dso,
                'description' => 'DSOs and large dental networks with unlimited scale.',
                'price_monthly' => 199900,
                'price_yearly' => 1999000,
                'max_branches' => null,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true]),
            );
        }
    }
}
