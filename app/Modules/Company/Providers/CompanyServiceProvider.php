<?php

namespace App\Modules\Company\Providers;

use App\Modules\Shared\Providers\AbstractModuleServiceProvider;

class CompanyServiceProvider extends AbstractModuleServiceProvider
{
    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Company';
    }
}
