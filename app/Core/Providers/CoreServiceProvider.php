<?php

namespace App\Core\Providers;

use App\Core\Audit\Events\ModelAudited;
use App\Core\Audit\Listeners\PersistAuditLog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Registra listeners globais do kernel (auditoria, bindings futuros).
 */
class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(ModelAudited::class, PersistAuditLog::class);
    }
}
