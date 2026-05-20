<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Carrega ServiceProviders de cada módulo de domínio.
 * Padrão modular: cada bounded context registra rotas, policies e bindings.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /** @var array<int, class-string<ServiceProvider>> */
    protected array $modules = [
        \App\Modules\Auth\Providers\AuthServiceProvider::class,
        \App\Modules\Company\Providers\CompanyServiceProvider::class,
        \App\Modules\Rbac\Providers\RbacServiceProvider::class,
        \App\Modules\Representative\Providers\RepresentativeServiceProvider::class,
        \App\Modules\Reseller\Providers\ResellerServiceProvider::class,
        \App\Modules\Customer\Providers\CustomerServiceProvider::class,
        \App\Modules\Product\Providers\ProductServiceProvider::class,
        \App\Modules\Consignment\Providers\ConsignmentServiceProvider::class,
        \App\Modules\Sale\Providers\SaleServiceProvider::class,
        \App\Modules\Analytics\Providers\AnalyticsServiceProvider::class,
        \App\Modules\ReturnOrder\Providers\ReturnOrderServiceProvider::class,
        \App\Modules\Commission\Providers\CommissionServiceProvider::class,
        \App\Modules\Financial\Providers\FinancialServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->modules as $provider)
        {
            $this->app->register($provider);
        }
    }
}
