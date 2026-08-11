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
        $tenant = Tenant::withoutEvents(function () use ($organization, $tenantId, $name): Tenant {
            $tenant = Tenant::query()->create([
                'id' => $tenantId,
                'organization_id' => $organization->id,
                'name' => $name ?? $tenantId,
                'slug' => $tenantId,
                'is_active' => true,
            ]);

            $tenant->domains()->create(['domain' => $tenantId.'.localhost']);
            $tenant->database()->manager()->createDatabase($tenant);

            $this->artisan('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--no-interaction' => true,
            ]);

            return $tenant;
        });

        $this->provisionedTenants[] = $tenant;

        return $tenant;
    }

    protected function tearDownProvisionedTenants(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        foreach ($this->provisionedTenants as $tenant) {
            try {
                $tenant->database()->manager()->deleteDatabase($tenant);
            } catch (\Throwable) {
                //
            }

            Tenant::withoutEvents(function () use ($tenant): void {
                $tenant->domains()->delete();
                $tenant->delete();
            });
        }

        $this->provisionedTenants = [];
    }
}
