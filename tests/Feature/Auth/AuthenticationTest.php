<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

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
}
