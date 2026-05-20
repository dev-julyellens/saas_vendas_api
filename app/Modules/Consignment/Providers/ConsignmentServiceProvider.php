<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Providers;

use App\Modules\Consignment\Events\ConsignmentClosed;
use App\Modules\Consignment\Events\ConsignmentDispatched;
use App\Modules\Consignment\Events\ConsignmentOperationRecorded;
use App\Modules\Consignment\Listeners\LogConsignmentOperation;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Policies\ConsignmentPolicy;
use App\Modules\Consignment\Repositories\ConsignmentItemRepository;
use App\Modules\Consignment\Repositories\ConsignmentOperationRepository;
use App\Modules\Consignment\Repositories\ConsignmentRepository;
use App\Modules\Consignment\Services\ConsignmentService;
use App\Modules\Product\Repositories\StockMovementRepository;
use App\Modules\Product\Services\StockMovementService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

class ConsignmentServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConsignmentRepository::class);
        $this->app->singleton(ConsignmentItemRepository::class);
        $this->app->singleton(ConsignmentOperationRepository::class);
        $this->app->singleton(StockMovementRepository::class);
        $this->app->singleton(StockMovementService::class);
        $this->app->singleton(ConsignmentService::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Consignment::class, ConsignmentPolicy::class);

        Event::listen(ConsignmentOperationRecorded::class, LogConsignmentOperation::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Consignment';
    }
}
