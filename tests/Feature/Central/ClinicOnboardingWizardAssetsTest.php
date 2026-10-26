<?php

namespace Tests\Feature\Central;

use App\Enums\PlatformRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicOnboardingWizardAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_create_page_includes_alpine_cdn_for_wizard(): void
    {
        $admin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $this->actingAs($admin)
            ->get(route('clinics.create'))
            ->assertOk()
            ->assertSee('cdn.jsdelivr.net/npm/alpinejs', false)
            ->assertSee('nextStep()', false)
            ->assertSee('name="logo"', false);
    }
}
