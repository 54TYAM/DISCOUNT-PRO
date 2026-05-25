<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role'       => ['required', Rule::in(['customer', 'manager', 'admin'])],
            'email'      => ['required', 'string', 'email'],
            'password'   => ['required', 'string'],
            'secret_key' => ['required_if:role,admin', 'nullable', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $intendedRole = $this->input('role');

        // ── Super-admin secret-key check ────────────────────────────────────
        if ($intendedRole === 'admin') {
            $expected = config('auth.super_admin_key', env('SUPER_ADMIN_SECRET_KEY'));
            if (! $expected) {
                throw ValidationException::withMessages([
                    'secret_key' => 'Super-admin sign-in is not configured on this server.',
                ]);
            }
            if (! hash_equals((string) $expected, (string) $this->input('secret_key', ''))) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'secret_key' => 'Incorrect super-admin secret key.',
                ]);
            }
        }

        // ── Email + password ────────────────────────────────────────────────
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // ── Pending-approval gate (managers awaiting super-admin approval) ──
        if (! $user->isApproved()) {
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Your account is awaiting super-admin approval. You will be able to sign in once it is approved.',
            ]);
        }

        // ── Role-tab match ─────────────────────────────────────────────────
        $actualRole = match (true) {
            $user->isAdmin()    => 'admin',
            $user->isManager()  => 'manager',
            default             => 'customer',
        };

        if ($actualRole !== $intendedRole) {
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();
            RateLimiter::hit($this->throttleKey());

            $label = ['customer' => 'Customer', 'manager' => 'Store Manager', 'admin' => 'Super Admin'][$actualRole];
            throw ValidationException::withMessages([
                'email' => "This account is registered as «{$label}». Please switch to that tab to sign in.",
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
