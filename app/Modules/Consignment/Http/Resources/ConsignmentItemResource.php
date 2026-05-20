<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsignmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn() => [
                'sku' => $this->product->sku,
                'name' => $this->product->name,
            ]),
            'quantity' => $this->quantity,
            'quantity_sold' => $this->quantity_sold,
            'quantity_returned' => $this->quantity_returned,
            'quantity_lost' => $this->quantity_lost,
            'quantity_damaged' => $this->quantity_damaged,
            'quantity_divergence' => $this->quantity_divergence,
            'quantity_pending' => $this->quantityPending(),
            'unit_price' => $this->unit_price,
        ];
    }
}
