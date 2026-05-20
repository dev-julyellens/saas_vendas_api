<?php

namespace App\Modules\Product\Notifications;

use App\Modules\Product\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo produto cadastrado')
            ->line("SKU: {$this->product->sku}")
            ->line("Nome: {$this->product->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
        ];
    }
}
