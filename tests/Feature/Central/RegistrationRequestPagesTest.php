<?php

namespace Tests\Feature\Central;

use App\Enums\PlatformRole;
use App\Enums\RegistrationRequestStatus;
use App\Models\OrganizationRegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRequestPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_registration_requests_index(): void
    {
        $admin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        OrganizationRegistrationRequest::factory()->create([
            'name' => 'Harbor Dental',
            'status' => RegistrationRequestStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route('registration-requests.index'))
            ->assertOk()
            ->assertSee(__('Registration Requests'))
            ->assertSee('Harbor Dental');
    }

    public function test_super_admin_can_view_registration_request_show(): void
    {
        $admin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $request = OrganizationRegistrationRequest::factory()->create([
            'name' => 'Bright Clinic',
            'contact_person' => 'Dr. Ada',
            'status' => RegistrationRequestStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route('registration-requests.show', $request))
            ->assertOk()
            ->assertSee('Bright Clinic')
            ->assertSee('Dr. Ada')
            ->assertSee(__('Approve request'));
    }
}
