<?php

namespace Tests\Feature\Central;

use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\ClinicCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClinicCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_admin_is_notified_when_clinic_is_created(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $suffix = fake()->unique()->lexify('????');

        $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Harbor Dental '.$suffix,
            'type' => 'single',
            'email' => 'contact@harbor-'.$suffix.'.test',
            'branch_name' => 'Harbor Dental '.$suffix.' - Main',
            'branch_slug' => 'harbor-'.$suffix.'-main',
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@harbor-'.$suffix.'.test',
            'admin_password' => 'password',
        ])->assertRedirect();

        $organization = Organization::query()->where('email', 'contact@harbor-'.$suffix.'.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@harbor-'.$suffix.'.test')->firstOrFail();
        $branch = $organization->adminBranch;

        $this->assertNotNull($branch);

        Notification::assertSentTo(
            $admin,
            ClinicCreatedNotification::class,
            function (ClinicCreatedNotification $notification) use ($organization, $branch): bool {
                return $notification->organization->is($organization)
                    && $notification->adminBranch->is($branch)
                    && $notification->plainPassword === 'password';
            },
        );
    }

    public function test_create_clinic_form_includes_admin_password_field(): void
    {
        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $this->actingAs($user)
            ->get(route('clinics.create'))
            ->assertOk()
            ->assertSee('name="admin_password"', false)
            ->assertSee(__('Primary Admin'));
    }
}
