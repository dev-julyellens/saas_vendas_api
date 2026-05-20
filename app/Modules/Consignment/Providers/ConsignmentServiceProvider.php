<?php

namespace App\Modules\Consignment\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class ConsignmentServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Consignment';
    }
}
