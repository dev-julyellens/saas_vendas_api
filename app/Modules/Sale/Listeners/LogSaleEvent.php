<?php

declare(strict_types=1);

namespace App\Modules\Sale\Listeners;

use App\Modules\Sale\Events\SaleCancelled;
use App\Modules\Sale\Events\SaleConfirmed;
use App\Modules\Sale\Events\SaleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogSaleEvent implements ShouldQueue
{
    public function handle(SaleCreated|SaleConfirmed|SaleCancelled $event): void
    {
        $sale = $event->sale;

        Log::channel('audit')->info('sale.' . class_basename($event), [
            'sale_id' => $sale->id,
            'sale_code' => $sale->code,
            'status' => $sale->status->value,
            'total' => (float) $sale->total,
            'company_id' => $sale->company_id,
            'reseller_id' => $sale->reseller_id,
            'consignment_id' => $sale->consignment_id,
        ]);
    }
}
