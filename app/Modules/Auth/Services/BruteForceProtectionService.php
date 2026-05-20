<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Proteção em camadas: throttle HTTP + contador por e-mail + lock na conta.
 */
class BruteForceProtectionService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function tooManyAttemptsForEmail(string $email): bool
    {
        $key = $this->emailThrottleKey($email);
        $max = (int) config('saas.auth.max_attempts_per_email', 5);

        return RateLimiter::tooManyAttempts($key, $max);
    }

    public function hitEmailAttempt(string $email): void
    {
        $decay = (int) config('saas.auth.email_attempt_decay_minutes', 15) * 60;
        RateLimiter::hit($this->emailThrottleKey($email), $decay);
    }

    public function clearEmailAttempts(string $email): void
    {
        RateLimiter::clear($this->emailThrottleKey($email));
    }

    public function recordFailedLogin(?User $user, string $email): void
    {
        $this->hitEmailAttempt($email);

        if ($user === null)
        {
            return;
        }

        $this->users->incrementFailedAttempts($user);
        $max = (int) config('saas.auth.max_login_attempts', 5);

        if ($user->fresh()->failed_login_attempts >= $max)
        {
            $minutes = (int) config('saas.auth.lockout_minutes', 15);
            $this->users->lockUntil($user, now()->addMinutes($minutes));
        }
    }

    public function clearForUser(User $user, string $email): void
    {
        $this->clearEmailAttempts($email);
        $this->users->resetFailedAttempts($user);
    }

    private function emailThrottleKey(string $email): string
    {
        return 'auth-email:' . mb_strtolower($email);
    }
}
