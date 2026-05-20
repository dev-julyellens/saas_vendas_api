<?php

namespace App\Core\Models\Concerns;

use App\Core\Audit\Events\ModelAudited;
use Illuminate\Database\Eloquent\Model;

/**
 * Dispara eventos de auditoria em create/update/delete/restore.
 * A persistência fica no Listener — separação de responsabilidades (SRP).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        foreach (['created', 'updated', 'deleted', 'restored'] as $event)
        {
            static::$event(function (Model $model) use ($event): void
            {
                event(new ModelAudited(
                    auditable: $model,
                    event: $event,
                    oldValues: $model->getOriginal(),
                    newValues: $model->getAttributes(),
                ));
            });
        }
    }
}
