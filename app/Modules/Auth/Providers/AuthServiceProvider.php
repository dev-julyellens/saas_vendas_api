<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Repositories\AccessLogRepository;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Repositories\UserSessionRepository;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\BruteForceProtectionService;
use App\Modules\Auth\Services\EmailVerificationService;
use App\Modules\Auth\Services\PasswordResetService;
use App\Modules\Auth\Services\SessionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(UserSessionRepository::class);
        $this->app->singleton(AccessLogRepository::class);
        $this->app->singleton(SessionService::class);
        $this->app->singleton(BruteForceProtectionService::class);
        $this->app->singleton(PasswordResetService::class);
        $this->app->singleton(EmailVerificationService::class);
        $this->app->singleton(AuthService::class);
    }

    public function boot(): void
    {
        Route::middleware(['api'])
            ->prefix('api/v1')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
