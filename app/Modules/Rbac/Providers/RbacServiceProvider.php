<?php

namespace App\Modules\Rbac\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class RbacServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Rbac';
    }
}
