<?php

namespace App\Core\Models\Concerns;

use App\Core\Models\Scopes\CompanyScope;
use App\Core\Tenant\TenantContext;
use App\Modules\Company\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait de isolamento multi-tenant — toda entidade de negócio deve usá-lo.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model): void
        {
            if ($model->company_id === null && TenantContext::hasCompany())
            {
                $model->company_id = TenantContext::companyId();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
