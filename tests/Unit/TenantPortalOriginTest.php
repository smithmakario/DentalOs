<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\Tenant;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TenantPortalOriginTest extends TestCase
{
    public function test_origin_prefers_request_host_and_port_over_app_url(): void
    {
        config(['app.url' => 'http://localhost']);

        $request = Request::create('http://127.0.0.1:8001/healing-stream/healing-stream-main/staff/login', 'GET');
        $this->app->instance('request', $request);
        URL::setRequest($request);

        $this->assertSame('http://127.0.0.1:8001', TenantPortal::origin($request));
    }

    public function test_login_url_keeps_request_origin_for_path_tenants(): void
    {
        config(['app.url' => 'http://localhost']);

        $organization = new Organization(['slug' => 'healing-stream']);
        $tenant = new Tenant([
            'id' => 'healing-stream-main',
            'slug' => 'healing-stream-main',
            'name' => 'Main',
        ]);
        $tenant->setRelation('organization', $organization);

        $request = Request::create('http://127.0.0.1:8001/healing-stream/healing-stream-main/staff/login', 'GET');

        $this->assertSame(
            'http://127.0.0.1:8001/healing-stream/healing-stream-main/staff/login',
            TenantPortal::loginUrl($tenant, $request),
        );
    }

    public function test_absolute_keeps_domain_tenant_paths_on_tenant_host(): void
    {
        config([
            'app.url' => 'http://localhost',
            'tenancy.central_domains' => ['localhost', '127.0.0.1'],
        ]);

        $organization = new Organization(['slug' => 'healing-stream']);
        $tenant = new Tenant([
            'id' => 'healing-stream-main',
            'slug' => 'healing-stream-main',
            'name' => 'Main',
        ]);
        $tenant->setRelation('organization', $organization);

        $request = Request::create('http://healing-stream-main.localhost/staff/login', 'POST');

        $this->assertSame(
            'http://healing-stream-main.localhost/dashboard',
            TenantPortal::absolute($tenant, '/dashboard', $request),
        );
    }

    public function test_absolute_uses_clinic_path_from_central_host(): void
    {
        config([
            'app.url' => 'http://localhost',
            'tenancy.central_domains' => ['localhost', '127.0.0.1'],
        ]);

        $organization = new Organization(['slug' => 'healing-stream']);
        $tenant = new Tenant([
            'id' => 'healing-stream-main',
            'slug' => 'healing-stream-main',
            'name' => 'Main',
        ]);
        $tenant->setRelation('organization', $organization);

        $request = Request::create('http://127.0.0.1:8001/clinic/login', 'POST');

        $this->assertSame(
            'http://127.0.0.1:8001/healing-stream/healing-stream-main/dashboard',
            TenantPortal::absolute($tenant, '/dashboard', $request),
        );
    }
}
