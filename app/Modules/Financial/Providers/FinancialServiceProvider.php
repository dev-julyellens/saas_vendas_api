<?php

namespace App\Modules\Financial\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class FinancialServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Financial';
    }
}
