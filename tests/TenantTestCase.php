<?php

namespace Tests;

use App\Models\Organization;
use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Organization $organization;

    protected string $tenantDomain;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(8));
        $tenantId = 'branch-'.$suffix;
        $this->tenantDomain = $tenantId.'.localhost';

        $this->organization = Organization::factory()->create();

        $this->tenant = Tenant::withoutEvents(function () use ($tenantId): Tenant {
            $tenant = Tenant::query()->create([
                'id' => $tenantId,
                'organization_id' => $this->organization->id,
                'name' => 'Test Branch',
                'slug' => $tenantId,
                'is_active' => true,
            ]);

            $tenant->domains()->create(['domain' => $this->tenantDomain]);

            return $tenant;
        });

        $this->tenant->database()->manager()->createDatabase($this->tenant);

        $this->artisan('tenants:migrate', [
            '--tenants' => [$this->tenant->id],
            '--no-interaction' => true,
        ]);
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if (isset($this->tenant)) {
            try {
                $this->tenant->database()->manager()->deleteDatabase($this->tenant);
            } catch (\Throwable) {
                //
            }

            Tenant::withoutEvents(function (): void {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            });
        }

        parent::tearDown();
    }

    protected function tenantUrl(string $path = '/'): string
    {
        return 'http://'.$this->tenantDomain.$path;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createStaff(array $attributes = []): Staff
    {
        return $this->tenant->run(fn (): Staff => Staff::factory()->create($attributes));
    }
}
