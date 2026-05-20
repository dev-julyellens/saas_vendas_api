<?php

namespace App\Modules\Reseller\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class ResellerServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Reseller';
    }
}
