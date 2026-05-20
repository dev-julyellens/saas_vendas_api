<?php

declare(strict_types=1);

namespace App\Modules\Commission\Providers;

use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Policies\CommissionPolicy;
use App\Modules\Commission\Repositories\CommissionRepository;
use App\Modules\Commission\Services\CommissionService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

class CommissionServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommissionRepository::class);
        $this->app->singleton(CommissionService::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Commission::class, CommissionPolicy::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Commission';
    }
}
