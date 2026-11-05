<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_login_fails_with_generic_error_for_unregistered_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'any-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah.',
        ]);
        $this->assertGuest();
    }

    public function test_login_fails_with_generic_error_for_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah.',
        ]);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_and_gets_specific_message(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Akun ini tidak aktif. Hubungi admin pusat.',
        ]);
        $this->assertGuest();
    }

    public function test_inactive_user_with_wrong_password_gets_generic_error(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah.',
        ]);
        $this->assertGuest();
    }

    public function test_login_throttling_triggers_l3_message_after_max_attempts(): void
    {
        $maxAttempts = (int) config('security.rate_limits.login_max_attempts', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->post('/login', [
                'email' => 'brute@example.com',
                'password' => 'wrong',
            ]);
        }

        // Attempt #6 should be blocked
        $response = $this->post('/login', [
            'email' => 'brute@example.com',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertStringStartsWith('Terlalu banyak percobaan. Coba lagi dalam', $errorMessage);
        $this->assertDatabaseHas('security_logs', [
            'event' => 'auth.lockout',
        ]);
    }

    public function test_session_id_regenerates_upon_successful_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->get('/login');
        $originalSessionId = session()->getId();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotEquals($originalSessionId, session()->getId());
    }

    public function test_unauthenticated_protected_route_access_redirects_with_expired_indicator(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login', ['session_expired' => 1]));
    }

    public function test_login_screen_renders_aria_attributes_and_error_alert_when_errors_exist(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'invalid',
        ]);

        $followUp = $this->get('/login');
        $followUp->assertStatus(200);
        $followUp->assertSee('role="alert"', false);
        $followUp->assertSee('aria-invalid="true"', false);
        $followUp->assertSee('aria-describedby="email-error"', false);
        $followUp->assertSee('login-card shake-error', false);
        $followUp->assertSee('Email atau kata sandi salah.');
    }

    public function test_expired_session_renders_l5_message_without_error_shake(): void
    {
        $response = $this->get('/login?session_expired=1');

        $response->assertStatus(200);
        $response->assertSee('Sesi Anda sudah berakhir. Silakan masuk kembali.');
        $response->assertDontSee('login-card shake-error', false);
    }
}
