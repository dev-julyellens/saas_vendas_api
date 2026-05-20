<?php

namespace App\Modules\Representative\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class RepresentativeServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Representative';
    }
}
