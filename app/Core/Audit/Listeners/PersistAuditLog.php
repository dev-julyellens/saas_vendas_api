<?php

namespace App\Core\Audit\Listeners;

use App\Core\Audit\Events\ModelAudited;
use App\Core\Tenant\TenantContext;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

/**
 * Auditoria assíncrona — não impacta latência da API em writes frequentes.
 */
class PersistAuditLog implements ShouldQueue
{
    public function handle(ModelAudited $event): void
    {
        $model = $event->auditable;

        AuditLog::query()->create([
            'company_id' => $model->company_id ?? TenantContext::companyId(),
            'user_id' => auth()->id(),
            'auditable_type' => $model::class,
            'auditable_id' => (string) $model->getKey(),
            'event' => $event->event,
            'old_values' => Arr::except($event->oldValues, ['password', 'remember_token']),
            'new_values' => Arr::except($event->newValues, ['password', 'remember_token']),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
