<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Listeners;

use App\Modules\Consignment\Events\ConsignmentOperationRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log estruturado complementar à auditoria Eloquent (access_log de domínio).
 */
class LogConsignmentOperation implements ShouldQueue
{
    public function handle(ConsignmentOperationRecorded $event): void
    {
        Log::channel('audit')->info('consignment.operation', [
            'consignment_id' => $event->consignment->id,
            'consignment_code' => $event->consignment->code,
            'operation_id' => $event->operation->id,
            'operation_type' => $event->operation->operation_type->value,
            'quantity' => $event->operation->quantity,
            'product_id' => $event->operation->product_id,
            'company_id' => $event->consignment->company_id,
        ]);
    }
}
