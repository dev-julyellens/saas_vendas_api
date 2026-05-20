<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Providers;

use App\Modules\Analytics\Repositories\AnalyticsRepository;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class AnalyticsServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AnalyticsRepository::class);
        $this->app->singleton(AnalyticsService::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Analytics';
    }
}
