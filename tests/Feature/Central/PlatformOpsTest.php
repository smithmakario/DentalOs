<?php

namespace Tests\Feature\Central;

use App\Enums\AuditAction;
use App\Enums\BillingCycle;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\PlatformPaymentSetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SubscriptionPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PlatformOpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_analytics_page(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee(__('Analytics'));
        $response->assertSee(__('Revenue Trend'));
    }

    public function test_org_admin_can_view_analytics_for_their_scope(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::OrgAdmin]);
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee(__('Organizations'));
    }

    public function test_super_admin_can_view_audit_logs(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::PaymentSettingsUpdated,
            'description' => 'Updated platform bank payment settings.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('audit-logs.index'));

        $response->assertOk();
        $response->assertSee(__('Audit Log'));
        $response->assertSee(__('Payment settings updated'));
    }

    public function test_org_admin_only_sees_their_organization_audit_logs(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::OrgAdmin]);
        $owned = Organization::factory()->create(['name' => 'Owned Clinic']);
        $other = Organization::factory()->create(['name' => 'Other Clinic']);
        $owned->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        AuditLog::query()->create([
            'action' => AuditAction::ClinicUpdated,
            'organization_id' => $owned->id,
            'description' => 'Updated owned clinic.',
            'created_at' => now(),
        ]);

        AuditLog::query()->create([
            'action' => AuditAction::ClinicUpdated,
            'organization_id' => $other->id,
            'description' => 'Updated other clinic.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Updated owned clinic.');
        $response->assertDontSee('Updated other clinic.');
    }

    public function test_clinic_update_creates_audit_log_entry(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create(['name' => 'Audit Test Clinic']);

        $this->actingAs($user)->patch(route('clinics.update', $organization), [
            'name' => 'Audit Test Clinic Updated',
            'type' => OrganizationType::Single->value,
            'email' => $organization->email,
            'phone' => $organization->phone,
            'address' => $organization->address,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::ClinicUpdated->value,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_manual_payment_verification_creates_audit_log_entry(): void
    {
        PlatformPaymentSetting::query()->create([
            'bank_name' => 'GTBank',
            'account_name' => 'DentaFlow',
            'account_number' => '1234567890',
            'currency' => 'NGN',
        ]);

        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();
        $plan = SubscriptionPlan::factory()->create();

        $payment = app(SubscriptionPaymentService::class)->submitManualPayment(
            organization: $organization,
            plan: $plan,
            billingCycle: BillingCycle::Monthly,
            paymentReference: 'REF-12345',
            notes: null,
        );

        $this->actingAs($user)->post(route('subscriptions.payments.verify', $payment));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::SubscriptionPaymentVerified->value,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_audit_log_service_records_actor_metadata(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();

        $this->actingAs($user);

        app(AuditLogService::class)->record(
            AuditAction::ClinicUpdated,
            'Test audit entry.',
            $organization,
            $organization,
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'description' => 'Test audit entry.',
        ]);
    }

    public function test_high_priority_audit_events_notify_super_admins(): void
    {
        Notification::fake();

        $superAdmin = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();

        $this->actingAs($superAdmin);

        app(AuditLogService::class)->record(
            AuditAction::ClinicOnboarded,
            'Onboarded clinic for alerts.',
            $organization,
            $organization,
        );

        Notification::assertSentTo($superAdmin, \App\Notifications\AuditEventAlert::class);
    }

    public function test_low_priority_audit_events_do_not_notify_super_admins(): void
    {
        Notification::fake();

        $superAdmin = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();

        $this->actingAs($superAdmin);

        app(AuditLogService::class)->record(
            AuditAction::ClinicUpdated,
            'Routine clinic update.',
            $organization,
            $organization,
        );

        Notification::assertNothingSent();
    }

    public function test_super_admin_can_export_analytics_csv(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->get(route('analytics.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }

    public function test_dashboard_shows_real_export_link_and_trends(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Export Report'));
        $response->assertSee(__('Last 30 days vs prior 30 days'));
    }
}
