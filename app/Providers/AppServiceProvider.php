<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Rate limiting — proteção contra brute-force e abuso de API.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request)
        {
            return Limit::perMinute((int) config('saas.api_rate_limit', 120))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request)
        {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute((int) config('saas.auth.max_attempts_per_email', 5))
                    ->by('email:' . mb_strtolower((string) $request->input('email'))),
            ];
        });

        RateLimiter::for('auth-password', function (Request $request)
        {
            return Limit::perMinute(5)->by(
                $request->input('email') ? 'email:' . mb_strtolower((string) $request->input('email')) : $request->ip()
            );
        });

        RateLimiter::for('auth-refresh', function (Request $request)
        {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
