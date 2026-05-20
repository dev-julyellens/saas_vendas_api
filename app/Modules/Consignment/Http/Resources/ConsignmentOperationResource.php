<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsignmentOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'operation_type' => $this->operation_type->value,
            'consignment_item_id' => $this->consignment_item_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'performed_by' => $this->whenLoaded('performedBy', fn() => [
                'id' => $this->performedBy->id,
                'name' => $this->performedBy->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
