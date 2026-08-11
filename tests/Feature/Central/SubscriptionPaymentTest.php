<?php

namespace Tests\Feature\Central;

use App\Enums\BillingCycle;
use App\Enums\OrganizationRole;
use App\Enums\PlatformPaymentMethod;
use App\Enums\PlatformRole;
use App\Enums\SubscriptionPaymentStatus;
use App\Models\Organization;
use App\Models\PlatformPaymentSetting;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\PaystackService;
use App\Services\SubscriptionPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_subscriptions_page(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->get(route('subscriptions.index'));

        $response->assertOk();
        $response->assertSee(__('Subscriptions & Billing'));
    }

    public function test_super_admin_can_save_bank_details(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);

        $response = $this->actingAs($user)->patch(route('subscriptions.settings.update'), [
            'bank_name' => 'First Bank',
            'account_name' => 'DentaFlow Systems',
            'account_number' => '0123456789',
            'bank_code' => '011',
            'currency' => 'NGN',
            'payment_instructions' => 'Use clinic name as payment reference.',
        ]);

        $response->assertRedirect(route('subscriptions.settings.edit'));
        $this->assertDatabaseHas('platform_payment_settings', [
            'bank_name' => 'First Bank',
            'account_number' => '0123456789',
        ]);
    }

    public function test_org_admin_cannot_access_bank_settings(): void
    {
        $user = User::factory()->create(['platform_role' => PlatformRole::OrgAdmin]);
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('subscriptions.settings.edit'));

        $response->assertForbidden();
    }

    public function test_manual_payment_submission_creates_awaiting_verification_record(): void
    {
        PlatformPaymentSetting::query()->create([
            'bank_name' => 'GTBank',
            'account_name' => 'DentaFlow',
            'account_number' => '1234567890',
            'currency' => 'NGN',
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $user = User::factory()->create(['platform_role' => PlatformRole::OrgAdmin]);
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->post(route('subscriptions.manual.store', $organization), [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
            'manual_payment_reference' => 'TXN-12345',
            'manual_notes' => 'Paid via mobile app',
        ]);

        $response->assertRedirect(route('subscriptions.index'));
        $this->assertDatabaseHas('subscription_payments', [
            'organization_id' => $organization->id,
            'payment_method' => PlatformPaymentMethod::Manual->value,
            'status' => SubscriptionPaymentStatus::AwaitingVerification->value,
            'manual_payment_reference' => 'TXN-12345',
        ]);
    }

    public function test_super_admin_can_verify_manual_payment(): void
    {
        PlatformPaymentSetting::query()->create([
            'bank_name' => 'GTBank',
            'account_name' => 'DentaFlow',
            'account_number' => '1234567890',
            'currency' => 'NGN',
        ]);

        $superAdmin = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $plan = SubscriptionPlan::factory()->create();
        $organization = Organization::factory()->create();

        $payment = app(SubscriptionPaymentService::class)->submitManualPayment(
            organization: $organization,
            plan: $plan,
            billingCycle: BillingCycle::Monthly,
            paymentReference: 'REF-999',
            notes: null,
        );

        $response = $this->actingAs($superAdmin)->post(route('subscriptions.payments.verify', $payment));

        $response->assertRedirect();
        $payment->refresh();
        $this->assertSame(SubscriptionPaymentStatus::Completed, $payment->status);
        $this->assertNotNull($payment->subscription->fresh()->current_period_end);
    }

    public function test_paystack_checkout_redirects_to_authorization_url(): void
    {
        config([
            'paystack.secret_key' => 'sk_test_example',
            'paystack.public_key' => 'pk_test_example',
        ]);

        $this->mock(PaystackService::class, function ($mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('amountToKobo')->andReturn(2990000);
            $mock->shouldReceive('initializeTransaction')
                ->once()
                ->andReturn([
                    'authorization_url' => 'https://checkout.paystack.com/test-session',
                    'access_code' => 'access_code_123',
                    'reference' => 'DF-TESTREF123',
                ]);
        });

        $plan = SubscriptionPlan::factory()->create();
        $user = User::factory()->create(['platform_role' => PlatformRole::SuperAdmin]);
        $organization = Organization::factory()->create();

        $response = $this->actingAs($user)->post(route('subscriptions.checkout.initiate', $organization), [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
            'payment_method' => PlatformPaymentMethod::Paystack->value,
        ]);

        $response->assertRedirect('https://checkout.paystack.com/test-session');
        $this->assertDatabaseHas('subscription_payments', [
            'organization_id' => $organization->id,
            'payment_method' => PlatformPaymentMethod::Paystack->value,
            'status' => SubscriptionPaymentStatus::Pending->value,
        ]);
    }
}
