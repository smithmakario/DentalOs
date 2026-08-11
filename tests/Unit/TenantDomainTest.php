<?php

namespace Tests\Unit;

use App\Support\TenantDomain;
use PHPUnit\Framework\TestCase;

class TenantDomainTest extends TestCase
{
    public function test_it_strips_scheme_port_and_path(): void
    {
        $this->assertSame('1woji.localhost', TenantDomain::normalize('http://1woji.localhost:8000/staff/login'));
    }

    public function test_it_lowercases_domain(): void
    {
        $this->assertSame('downtown.localhost', TenantDomain::normalize('Downtown.Localhost'));
    }

    public function test_it_returns_null_for_null_input(): void
    {
        $this->assertNull(TenantDomain::normalize(null));
    }
}
