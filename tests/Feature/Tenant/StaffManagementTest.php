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

        $this->organization->update(['admin_branch_id' => $this->tenant->id]);

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
            $this->assertTrue(
                str_contains($member->avatarUrl(), '/tenancy/assets/')
                || str_contains($member->avatarUrl(), '/media/'),
            );
        });
    }

    public function test_primary_admin_can_create_clinic_admin_on_admin_branch(): void
    {
        $this->organization->update(['admin_branch_id' => $this->tenant->id]);

        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'current-admin@clinic.test',
        ]);

        $response = $this->actingAs($admin, 'staff')->post($this->tenantUrl('/staff'), [
            'first_name' => 'New',
            'last_name' => 'Admin',
            'email' => 'new-admin@clinic.test',
            'password' => 'password123',
            'role' => StaffRole::ClinicAdmin->value,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->tenant->run(function () use ($admin): void {
            $newAdmin = Staff::query()->where('email', 'new-admin@clinic.test')->first();
            $previous = Staff::query()->find($admin->id);

            $this->assertNotNull($newAdmin);
            $this->assertSame(StaffRole::ClinicAdmin, $newAdmin->role);
            $this->assertNotNull($previous);
            $this->assertSame(StaffRole::Receptionist, $previous->role);
            $this->assertSame(1, Staff::query()->where('role', StaffRole::ClinicAdmin)->count());
        });

        // Follow the redirect: show must stay on the tenant DB (not central dental_os).
        $this->actingAs($admin, 'staff')
            ->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('new-admin@clinic.test');
    }

    public function test_cannot_create_clinic_admin_on_non_admin_branch(): void
    {
        // No HQ assigned → this branch cannot assign Clinic Admin.
        $this->organization->update(['admin_branch_id' => null]);

        $admin = $this->createStaff([
            'role' => StaffRole::ClinicAdmin,
            'email' => 'synced-admin@clinic.test',
        ]);

        $create = $this->actingAs($admin, 'staff')->get($this->tenantUrl('/staff/create'));
        $create->assertOk();
        $this->assertStringNotContainsString(
            'value="'.StaffRole::ClinicAdmin->value.'"',
            $create->getContent(),
        );

        $response = $this->actingAs($admin, 'staff')->post($this->tenantUrl('/staff'), [
            'first_name' => 'Branch',
            'last_name' => 'Admin',
            'email' => 'branch-admin@clinic.test',
            'password' => 'password123',
            'role' => StaffRole::ClinicAdmin->value,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('role');

        $this->tenant->run(function (): void {
            $this->assertFalse(
                Staff::query()->where('email', 'branch-admin@clinic.test')->exists(),
            );
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
