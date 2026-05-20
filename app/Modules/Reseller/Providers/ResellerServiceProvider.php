<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Providers;

use App\Modules\Reseller\Models\Reseller;
use App\Modules\Reseller\Policies\ResellerPolicy;
use App\Modules\Reseller\Repositories\ResellerRepository;
use App\Modules\Reseller\Services\ResellerService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

class ResellerServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ResellerRepository::class);
        $this->app->singleton(ResellerService::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Reseller::class, ResellerPolicy::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Reseller';
    }
}
