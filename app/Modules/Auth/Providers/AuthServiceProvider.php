<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Auth — rotas públicas (login) e protegidas (me/logout) sem middleware tenant.company no login.
 */
class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthService::class, fn($app) => new AuthService(
            $app->make(UserRepository::class)
        ));
    }

    public function boot(): void
    {
        Route::middleware(['api'])
            ->prefix('api/v1')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
