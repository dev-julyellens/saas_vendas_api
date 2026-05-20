<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Providers;

use App\Modules\Rbac\Services\RbacService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class RbacServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RbacService::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Rbac';
    }
}
