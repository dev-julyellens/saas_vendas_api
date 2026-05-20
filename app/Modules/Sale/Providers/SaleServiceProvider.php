<?php

namespace App\Modules\Sale\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class SaleServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Sale';
    }
}
