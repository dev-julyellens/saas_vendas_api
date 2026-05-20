<?php

namespace App\Modules\Customer\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class CustomerServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Customer';
    }
}
