<?php

namespace Tests\Concerns;

use App\Models\Organization;
use App\Models\Tenant;

trait CreatesTenantBranches
{
    /** @var list<Tenant> */
    private array $provisionedTenants = [];

    protected function createTenantBranch(Organization $organization, string $tenantId, ?string $name = null): Tenant
    {
        $tenant = Tenant::query()->create([
            'id' => $tenantId,
            'organization_id' => $organization->id,
            'name' => $name ?? $tenantId,
            'slug' => $tenantId,
            'is_active' => true,
        ]);

        $tenant->domains()->create(['domain' => $tenantId.'.localhost']);

        $this->provisionedTenants[] = $tenant;

        return $tenant;
    }

    protected function tearDownProvisionedTenants(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        foreach ($this->provisionedTenants as $tenant) {
            Tenant::withoutEvents(function () use ($tenant): void {
                $tenant->domains()->delete();
                $tenant->delete();
            });
        }

        $this->provisionedTenants = [];
    }
}
