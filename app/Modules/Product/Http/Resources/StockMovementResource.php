<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'reseller_id' => $this->reseller_id,
            'movement_type' => $this->movement_type->value,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'consignment_operation_id' => $this->consignment_operation_id,
            'notes' => $this->notes,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
        ];
    }
}
