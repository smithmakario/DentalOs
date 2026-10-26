<?php

namespace Tests\Feature\Central;

use App\Enums\OrganizationRole;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\Staff;
use App\Models\User;
use App\Notifications\ClinicPrimaryAdminUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesTenantBranches;
use Tests\TestCase;

class ClinicPrimaryAdminUpdatedNotificationTest extends TestCase
{
    use CreatesTenantBranches;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->tearDownProvisionedTenants();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    public function test_primary_admin_is_notified_when_email_changes(): void
    {
        Notification::fake();

        $organization = Organization::factory()->create();
        $tenant = $this->createTenantBranch(
            $organization,
            'admin-'.Str::lower(fake()->unique()->lexify('????')),
            'Admin Branch',
        );
        $organization->update(['admin_branch_id' => $tenant->id]);

        $admin = User::factory()->create([
            'name' => 'Old Admin',
            'email' => 'old-admin@clinic.test',
            'password' => 'password',
            'platform_role' => PlatformRole::OrgAdmin,
        ]);
        $organization->users()->attach($admin->id, ['role' => OrganizationRole::Owner->value]);

        $tenant->run(function (): void {
            Staff::factory()->create([
                'first_name' => 'Old',
                'last_name' => 'Admin',
                'email' => 'old-admin@clinic.test',
                'password' => 'password',
                'role' => StaffRole::ClinicAdmin,
            ]);
        });

        $superAdmin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $this->actingAs($superAdmin)->patch(route('clinics.primary-admin.update', $organization), [
            'admin_name' => 'New Admin',
            'admin_email' => 'new-admin@clinic.test',
            'admin_password' => 'new-password',
            'admin_password_confirmation' => 'new-password',
        ])->assertRedirect(route('clinics.show', $organization));

        $admin->refresh();

        Notification::assertSentTo(
            $admin,
            ClinicPrimaryAdminUpdatedNotification::class,
            function (ClinicPrimaryAdminUpdatedNotification $notification) use ($organization, $tenant): bool {
                return $notification->organization->is($organization)
                    && $notification->adminBranch?->is($tenant)
                    && $notification->previousEmail === 'old-admin@clinic.test'
                    && $notification->plainPassword === 'new-password';
            },
        );
    }

    public function test_primary_admin_is_not_notified_when_only_name_changes(): void
    {
        Notification::fake();

        $organization = Organization::factory()->create();
        $tenant = $this->createTenantBranch(
            $organization,
            'admin-'.Str::lower(fake()->unique()->lexify('????')),
            'Admin Branch',
        );
        $organization->update(['admin_branch_id' => $tenant->id]);

        $admin = User::factory()->create([
            'name' => 'Same Email Admin',
            'email' => 'same@clinic.test',
            'password' => 'password',
            'platform_role' => PlatformRole::OrgAdmin,
        ]);
        $organization->users()->attach($admin->id, ['role' => OrganizationRole::Owner->value]);

        $tenant->run(function (): void {
            Staff::factory()->create([
                'email' => 'same@clinic.test',
                'role' => StaffRole::ClinicAdmin,
            ]);
        });

        $superAdmin = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $this->actingAs($superAdmin)->patch(route('clinics.primary-admin.update', $organization), [
            'admin_name' => 'Renamed Admin',
            'admin_email' => 'same@clinic.test',
        ])->assertRedirect(route('clinics.show', $organization));

        Notification::assertNothingSent();
    }
}
