<?php

namespace Tests\Unit;

use App\Support\BranchIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchIdentifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_suggests_branch_name_from_clinic_name(): void
    {
        $this->assertSame(
            'BrightSmile Dental - '.__('Main'),
            BranchIdentifier::suggestBranchName('BrightSmile Dental'),
        );
    }

    public function test_base_host_comes_from_app_url(): void
    {
        config(['app.url' => 'https://dental.example.com']);

        $this->assertSame('dental.example.com', BranchIdentifier::baseHost());
    }

    public function test_fill_missing_generates_slug_without_domain(): void
    {
        $filled = BranchIdentifier::fillMissing([
            'name' => 'Bright Dental',
            'branch_name' => '',
            'branch_slug' => '',
        ]);

        $this->assertSame('Bright Dental - '.__('Main'), $filled['branch_name']);
        $this->assertSame('bright-dental-main', $filled['branch_slug']);
        $this->assertArrayNotHasKey('domain', $filled);
    }
}
