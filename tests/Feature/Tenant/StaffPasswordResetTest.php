<?php

namespace Tests\Feature\Tenant;

use App\Models\Staff;
use App\Notifications\StaffResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TenantTestCase;

class StaffPasswordResetTest extends TenantTestCase
{
    public function test_staff_login_page_shows_forgot_password_link(): void
    {
        $response = $this->get($this->tenantUrl('/staff/login'));

        $response->assertOk();
        $response->assertSee(__('Forgot Password?'));
    }

    public function test_staff_can_view_forgot_password_page(): void
    {
        $response = $this->get($this->tenantUrl('/staff/forgot-password'));

        $response->assertOk();
        $response->assertSee(__('Forgot Password'));
        $response->assertSee('Test Branch');
    }

    public function test_staff_can_request_password_reset_link(): void
    {
        Notification::fake();

        $staff = $this->createStaff([
            'email' => 'dentist@branch.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post($this->tenantUrl('/staff/forgot-password'), [
            'email' => $staff->email,
        ]);

        $response->assertSessionHas('status');

        Notification::assertSentTo($staff, StaffResetPassword::class);
    }

    public function test_staff_can_view_reset_password_page(): void
    {
        Notification::fake();

        $staff = $this->createStaff([
            'email' => 'dentist@branch.test',
            'password' => Hash::make('password'),
        ]);

        $this->post($this->tenantUrl('/staff/forgot-password'), [
            'email' => $staff->email,
        ]);

        Notification::assertSentTo($staff, StaffResetPassword::class, function ($notification): bool {
            $response = $this->get($this->tenantUrl('/staff/reset-password/'.$notification->token.'?email=dentist@branch.test'));

            $response->assertOk();
            $response->assertSee(__('Reset Password'));

            return true;
        });
    }

    public function test_staff_can_reset_password_with_valid_token(): void
    {
        Notification::fake();

        $staff = $this->createStaff([
            'email' => 'dentist@branch.test',
            'password' => Hash::make('old-password'),
        ]);

        $this->post($this->tenantUrl('/staff/forgot-password'), [
            'email' => $staff->email,
        ]);

        Notification::assertSentTo($staff, StaffResetPassword::class, function ($notification) use ($staff): bool {
            $response = $this->post($this->tenantUrl('/staff/reset-password'), [
                'token' => $notification->token,
                'email' => $staff->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect($this->tenantUrl('/staff/login'));

            return true;
        });

        $this->tenant->run(function (): void {
            $staff = Staff::query()->where('email', 'dentist@branch.test')->firstOrFail();

            $this->assertTrue(Hash::check('new-password', $staff->password));
        });
    }
}
