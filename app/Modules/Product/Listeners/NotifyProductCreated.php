<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Jobs\SyncProductCatalogJob;
use App\Modules\Product\Notifications\ProductCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Listener enfileira Job e Notification — padrão para side-effects assíncronos.
 */
class NotifyProductCreated implements ShouldQueue
{
    public function handle(ProductCreated $event): void
    {
        SyncProductCatalogJob::dispatch($event->product->id, $event->product->company_id);

        // Exemplo: notificar admins da empresa quando houver usuários configurados.
        // Notification::send($admins, new ProductCreatedNotification($event->product));
    }
}
