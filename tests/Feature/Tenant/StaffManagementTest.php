<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class StaffManagementTest extends TenantTestCase
{
    public function test_clinic_admin_can_manage_staff(): void
    {
        Storage::fake('public');

        $admin = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        $index = $this->actingAs($admin, 'staff')->get($this->tenantUrl('/staff'));
        $index->assertOk();
        $index->assertSee(__('Staff Management'));

        $response = $this->actingAs($admin, 'staff')->post($this->tenantUrl('/staff'), [
            'first_name' => 'Ada',
            'last_name' => 'Okonkwo',
            'email' => 'ada.okonkwo@example.com',
            'phone' => '+2348012345678',
            'password' => 'password123',
            'role' => StaffRole::Dentist->value,
            'specialization' => 'Orthodontics',
            'license_number' => 'MDCN-12345',
            'years_of_experience' => 8,
            'is_active' => true,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect();

        $this->tenant->run(function (): void {
            $member = Staff::query()->where('email', 'ada.okonkwo@example.com')->first();

            $this->assertNotNull($member);
            $this->assertSame('Orthodontics', $member->specialization);
            $this->assertSame(8, $member->years_of_experience);
            $this->assertNotNull($member->avatar_path);
            $this->assertStringContainsString('/tenancy/assets/', $member->avatarUrl());
        });
    }

    public function test_receptionist_cannot_manage_staff(): void
    {
        $receptionist = $this->createStaff(['role' => StaffRole::Receptionist]);

        $response = $this->actingAs($receptionist, 'staff')->get($this->tenantUrl('/staff'));

        $response->assertForbidden();
    }

    public function test_clinic_admin_cannot_deactivate_self(): void
    {
        $admin = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        $response = $this->actingAs($admin, 'staff')->delete($this->tenantUrl('/staff/'.$admin->id));

        $response->assertForbidden();
    }
}
