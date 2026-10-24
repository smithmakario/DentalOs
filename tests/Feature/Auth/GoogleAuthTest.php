<?php

namespace Tests\Feature\Auth;

use App\Enums\PlatformRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_google_redirect_is_available(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_platform_google_callback_logs_in_existing_super_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'superadmin@dentalos.test',
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $this->withSession([
            'google_oauth_context' => 'platform',
            'google_oauth_return' => route('login'),
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-user-1');
        $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
        $socialiteUser->shouldReceive('getName')->andReturn($user->name);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-user-1', $user->fresh()->google_id);
    }

    public function test_platform_google_callback_rejects_unknown_email(): void
    {
        $this->withSession([
            'google_oauth_context' => 'platform',
            'google_oauth_return' => route('login'),
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-user-2');
        $socialiteUser->shouldReceive('getEmail')->andReturn('unknown@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Unknown');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
