<?php

namespace Tests\Feature\Central;

use App\Enums\PlatformRole;
use App\Enums\RegistrationRequestStatus;
use App\Models\OrganizationRegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_shows_registration_notifications(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        OrganizationRegistrationRequest::factory()->create([
            'name' => 'Bright Smile Request',
            'email' => 'owner@bright-smile.test',
            'status' => RegistrationRequestStatus::Pending,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Notifications'));
        $response->assertSee(__('New registration request'));
        $response->assertSee('Bright Smile Request');
    }
}
