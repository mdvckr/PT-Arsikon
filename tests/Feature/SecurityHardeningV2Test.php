<?php

namespace Tests\Feature;

use App\Models\SecurityLog;
use App\Models\User;
use App\Services\SecurityLogger;
use App\Services\SecureUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningV2Test extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);
        $viewAuditPerm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view audit logs', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email' => 'admin.test@arsikon.com',
            'password' => Hash::make('SuperSecretPassword123!'),
            'is_active' => true,
        ]);
        $this->adminUser->assignRole('Super Admin');
        $this->adminUser->givePermissionTo($viewAuditPerm);

        RateLimiter::clear('ip:127.0.0.1');
        RateLimiter::clear('email:admin.test@arsikon.com|127.0.0.1');
    }

    /**
     * Test 1: Layer 3.4 - Security Headers & CSP Report-Only
     */
    public function test_layer_3_4_security_headers_and_csp(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
        $this->assertTrue(
            $response->headers->has('Content-Security-Policy-Report-Only') ||
            $response->headers->has('Content-Security-Policy')
        );
    }

    /**
     * Test 2: Layer 4.2 - Rate Limiting & 15-Minute Lockout
     */
    public function test_layer_4_2_login_locks_out_after_five_failed_attempts(): void
    {
        $email = 'bruteforce@arsikon.com';

        // 5 percobaaan gagal
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword123!',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // Percobaan ke-6 harus ditolak oleh RateLimiter (Lockout 15 menit)
        $blockedResponse = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword123!',
        ]);

        $blockedResponse->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Terlalu banyak', $errors[0] ?? '');
    }

    /**
     * Test 3: Layer 4.2 - Default Password Complexity Rules
     */
    public function test_layer_4_2_password_default_rules_enforce_complexity(): void
    {
        // Sandi lemah: < 10 karakter
        $validator1 = Validator::make(['password' => 'Short1!'], [
            'password' => ['required', Password::defaults()],
        ]);
        $this->assertTrue($validator1->fails());

        // Sandi lemah: tanpa simbol
        $validator2 = Validator::make(['password' => 'PasswordTanpaSimbol123'], [
            'password' => ['required', Password::defaults()],
        ]);
        $this->assertTrue($validator2->fails());

        // Sandi lemah: tanpa huruf kapital
        $validator3 = Validator::make(['password' => 'passwordkecil123!'], [
            'password' => ['required', Password::defaults()],
        ]);
        $this->assertTrue($validator3->fails());

        // Sandi kuat: >= 10 karakter, huruf besar & kecil, angka, simbol
        $validator4 = Validator::make(['password' => 'KombinasiKuat#2026Secure!'], [
            'password' => ['required', Password::defaults()],
        ]);
        $this->assertFalse($validator4->fails());
    }

    /**
     * Test 4: Layer 4.2 - AuthenticateSession middleware attached to web routes
     */
    public function test_layer_4_2_authenticate_session_middleware_is_registered(): void
    {
        $route = app('router')->getRoutes()->getByName('dashboard');
        $this->assertNotNull($route);
        $middleware = $route->gatherMiddleware();

        $this->assertTrue(
            in_array('auth.session', $middleware) ||
            in_array(\Illuminate\Session\Middleware\AuthenticateSession::class, $middleware)
        );
    }

    /**
     * Test 5: Layer 7.1 - Security Logs records successful login
     */
    public function test_layer_7_1_security_log_records_login_success(): void
    {
        $response = $this->post('/login', [
            'email' => $this->adminUser->email,
            'password' => 'SuperSecretPassword123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->adminUser);

        $log = SecurityLog::where('event', 'auth.login_success')
            ->where('user_id', $this->adminUser->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('info', $log->severity);
        $this->assertEquals($this->adminUser->email, $log->details['email'] ?? null);
    }

    /**
     * Test 6: Layer 7.1 - Security Logs records login failure without leaking passwords
     */
    public function test_layer_7_1_security_log_records_login_failure_sanitized(): void
    {
        $attemptedEmail = 'attacker@target.com';
        $sensitivePassword = 'PlainSecretPayloadPassword999!';

        $this->post('/login', [
            'email' => $attemptedEmail,
            'password' => $sensitivePassword,
        ]);

        $log = SecurityLog::where('event', 'auth.login_failed')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('warning', $log->severity);
        $this->assertEquals($attemptedEmail, $log->details['attempted_email'] ?? null);

        // Memastikan password polos TIDAK PERNAH tersimpan di database
        $this->assertStringNotContainsString($sensitivePassword, json_encode($log->toArray()));
    }

    /**
     * Test 7: Layer 7.1 - SecurityLogger sanitizes sensitive payload keys
     */
    public function test_layer_7_1_security_logger_sanitizes_keys(): void
    {
        $raw = [
            'username' => 'budi',
            'password' => 'secret123',
            'api_token' => 'bearer-xyz',
            'nested' => [
                'current_password' => 'pass456',
                'description' => 'valid data',
            ],
        ];

        $sanitized = SecurityLogger::sanitize($raw);

        $this->assertEquals('budi', $sanitized['username']);
        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['api_token']);
        $this->assertEquals('[REDACTED]', $sanitized['nested']['current_password']);
        $this->assertEquals('valid data', $sanitized['nested']['description']);
    }

    /**
     * Test 8: Layer 7.1 - Security Logs records role changes
     */
    public function test_layer_7_1_security_log_records_role_changes(): void
    {
        $targetUser = User::factory()->create([
            'email' => 'target.user@arsikon.com',
            'is_active' => true,
        ]);

        SecurityLogger::logRoleOrPermissionChange($targetUser, [
            'action' => 'assigned_role',
            'roles' => ['Admin Pusat'],
        ], $this->adminUser);

        $log = SecurityLog::where('event', 'admin.role_change')
            ->where('details->target_user_id', $targetUser->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('critical', $log->severity);
        $this->assertEquals($this->adminUser->id, $log->user_id);
    }

    /**
     * Test 9: Layer 5 - File Upload validation rejects disguised scripts and dangerous extensions
     */
    public function test_layer_5_file_upload_validation_rejects_disguised_script(): void
    {
        $rules = ['file' => SecureUploadService::rules(true, 5120, true)];

        // Berkas skrip PHP dengan MIME type teks/skrip terdeteksi
        $disguisedFile = UploadedFile::fake()->create('shell.jpg', 50, 'text/x-php');
        $validator1 = Validator::make(['file' => $disguisedFile], $rules);
        $this->assertTrue($validator1->fails());

        // Berkas berekstensi .php langsung ditolak oleh mimes
        $phpFile = UploadedFile::fake()->create('backdoor.php', 50, 'application/x-php');
        $validator2 = Validator::make(['file' => $phpFile], $rules);
        $this->assertTrue($validator2->fails());

        // Percobaan proses berkas berbahaya langsung ditolak dengan exception oleh SecureUploadService
        $this->expectException(\InvalidArgumentException::class);
        SecureUploadService::processAndStore($phpFile, 'uploads');
    }

    /**
     * Test 10: Layer 5 - Legitimate image upload passes validation
     */
    public function test_layer_5_legitimate_image_passes_validation(): void
    {
        $rules = ['file' => SecureUploadService::rules(true, 5120, true)];

        $validImage = UploadedFile::fake()->image('nota.png', 800, 600);
        $validator = Validator::make(['file' => $validImage], $rules);
        $this->assertFalse($validator->fails());
    }

    /**
     * Test 11: Layer 7.1 - Security events are bridged to audit_logs (Cara B Dual-Write)
     */
    public function test_layer_7_security_events_are_bridged_to_audit_logs(): void
    {
        // 1. Trigger lockout security log
        SecurityLogger::logLockout('locked.account@arsikon.com');

        $auditLog = \App\Models\AuditLog::where('event', 'SECURITY_LOCKOUT')->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals('Security', $auditLog->auditable_type);
        $this->assertEquals('critical', $auditLog->new_values['severity'] ?? null);
        $this->assertStringContainsString('Peringatan Keamanan', $auditLog->new_values['summary'] ?? '');

        // 2. Memastikan halaman audit log merender event keamanan dengan benar
        $response = $this->actingAs($this->adminUser)->get(route('audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('SECURITY_LOCKOUT');
        $response->assertSee('CRITICAL');
        $response->assertSee('Peringatan Keamanan');
    }
}
