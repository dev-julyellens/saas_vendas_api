<?php

namespace App\Modules\Shared\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Provider base de módulo — padroniza prefixo de rotas e namespace.
 */
abstract class AbstractModuleServiceProvider extends ServiceProvider
{
    abstract protected function modulePath(): string;

    abstract protected function moduleNamespace(): string;

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrationsFrom($this->modulePath() . '/Database/Migrations');
    }

    protected function loadRoutes(): void
    {
        $routesFile = $this->modulePath() . '/Routes/api.php';

        if (! is_file($routesFile))
        {
            return;
        }

        Route::middleware(['api', 'auth.api', 'tenant', 'tenant.company'])
            ->prefix('api/v1')
            ->group($routesFile);
    }
}
