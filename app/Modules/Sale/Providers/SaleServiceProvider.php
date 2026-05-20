<?php

declare(strict_types=1);

namespace App\Modules\Sale\Providers;

use App\Modules\Commission\Repositories\CommissionRepository;
use App\Modules\Commission\Services\CommissionService;
use App\Modules\Consignment\Repositories\ConsignmentItemRepository;
use App\Modules\Product\Repositories\StockMovementRepository;
use App\Modules\Product\Services\StockMovementService;
use App\Modules\Sale\Events\SaleCancelled;
use App\Modules\Sale\Events\SaleConfirmed;
use App\Modules\Sale\Events\SaleCreated;
use App\Modules\Sale\Listeners\LogSaleEvent;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Policies\SalePolicy;
use App\Modules\Sale\Repositories\SaleItemRepository;
use App\Modules\Sale\Repositories\SaleRepository;
use App\Modules\Sale\Services\SaleService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

class SaleServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SaleRepository::class);
        $this->app->singleton(SaleItemRepository::class);
        $this->app->singleton(CommissionRepository::class);
        $this->app->singleton(CommissionService::class);
        $this->app->singleton(ConsignmentItemRepository::class);
        $this->app->singleton(StockMovementRepository::class);
        $this->app->singleton(StockMovementService::class);
        $this->app->singleton(SaleService::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Sale::class, SalePolicy::class);

        Event::listen(SaleCreated::class, LogSaleEvent::class);
        Event::listen(SaleConfirmed::class, LogSaleEvent::class);
        Event::listen(SaleCancelled::class, LogSaleEvent::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Sale';
    }
}
