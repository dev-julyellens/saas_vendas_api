<?php

namespace App\Modules\ReturnOrder\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class ReturnOrderServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\ReturnOrder';
    }
}
