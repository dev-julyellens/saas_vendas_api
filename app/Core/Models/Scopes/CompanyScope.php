<?php

namespace App\Core\Models\Scopes;

use App\Core\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope — garante que TODA query em modelos tenant-aware
 * filtre automaticamente por company_id, prevenindo vazamento de dados.
 */
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::shouldBypassScope())
        {
            return;
        }

        $companyId = TenantContext::companyId();

        if ($companyId === null)
        {
            return;
        }

        $builder->where($model->getTable() . '.company_id', $companyId);
    }
}
