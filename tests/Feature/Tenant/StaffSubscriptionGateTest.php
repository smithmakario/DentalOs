<?php

namespace Tests\Feature\Tenant;

use App\Enums\BillingCycle;
use App\Enums\OrganizationSubscriptionStatus;
use App\Models\OrganizationSubscription;
use Illuminate\Support\Facades\Hash;
use Tests\TenantTestCase;

class StaffSubscriptionGateTest extends TenantTestCase
{
    public function test_staff_can_login_when_subscription_is_current(): void
    {
        $staff = $this->createStaff([
            'email' => 'active@branch.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post($this->tenantUrl('/staff/login'), [
            'email' => $staff->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($staff, 'staff');
    }

    public function test_staff_cannot_login_after_payment_grace_expires(): void
    {
        OrganizationSubscription::query()
            ->where('organization_id', $this->organization->id)
            ->update([
                'status' => OrganizationSubscriptionStatus::Active->value,
                'billing_cycle' => BillingCycle::Monthly->value,
                'current_period_start' => now()->subMonths(2),
                'current_period_end' => now()->subDays(6),
            ]);

        $staff = $this->createStaff([
            'email' => 'overdue@branch.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->from($this->tenantUrl('/staff/login'))->post($this->tenantUrl('/staff/login'), [
            'email' => $staff->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($this->tenantUrl('/staff/login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest('staff');
    }
}
