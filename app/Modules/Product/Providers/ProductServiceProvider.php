<?php

namespace App\Modules\Product\Providers;

use App\Modules\Product\Policies\ProductPolicy;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepository;
use App\Modules\Product\Services\ProductService;
use App\Modules\Shared\Providers\AbstractModuleServiceProvider;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Listeners\NotifyProductCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

class ProductServiceProvider extends AbstractModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(ProductService::class);
    }

    public function boot(): void
    {
        parent::boot();
        Gate::policy(Product::class, ProductPolicy::class);
        Event::listen(ProductCreated::class, NotifyProductCreated::class);
    }

    protected function modulePath(): string
    {
        return __DIR__ . '/..';
    }

    protected function moduleNamespace(): string
    {
        return 'App\\Modules\\Product';
    }
}
