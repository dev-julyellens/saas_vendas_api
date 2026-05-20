<?php

namespace App\Modules\Commission\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class CommissionServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Commission';
    }
}
