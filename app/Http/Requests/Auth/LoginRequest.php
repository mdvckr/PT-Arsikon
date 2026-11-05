<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Custom validation messages for field-level errors (L2).
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = Str::lower((string) $this->input('email'));
        $user = User::where('email', $email)->first();

        $lockoutSeconds = (int) config('security.rate_limits.lockout_minutes', 15) * 60;

        if (! $user || ! Hash::check((string) $this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey(), $lockoutSeconds);
            SecurityLogger::logLoginFailed($email, $this, 'Invalid credentials');

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($this->throttleKey(), $lockoutSeconds);
            SecurityLogger::logLoginFailed($email, $this, 'Account inactive');

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak aktif. Hubungi admin pusat.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
        SecurityLogger::logLoginSuccess($user, $this);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $maxAttempts = (int) config('security.rate_limits.login_max_attempts', 5);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));
        SecurityLogger::logLockout($this->input('email'), $this);

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = (int) ceil($seconds / 60);

        $timeMessage = $seconds >= 60 ? "{$minutes} menit." : "{$seconds} detik.";

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$timeMessage}",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
