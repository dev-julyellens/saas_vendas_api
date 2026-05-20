<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'reseller_id' => $this->reseller_id,
            'representative_id' => $this->representative_id,
            'reseller' => $this->whenLoaded('reseller', fn() => [
                'id' => $this->reseller->id,
                'name' => $this->reseller->name,
            ]),
            'representative' => $this->whenLoaded('representative', fn() => [
                'id' => $this->representative?->id,
                'name' => $this->representative?->name,
            ]),
            'consigned_at' => $this->consigned_at?->toDateString(),
            'expected_return_at' => $this->expected_return_at?->toDateString(),
            'dispatched_at' => $this->dispatched_at?->toIso8601String(),
            'collected_at' => $this->collected_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'notes' => $this->notes,
            'items' => ConsignmentItemResource::collection($this->whenLoaded('items')),
            'operations' => ConsignmentOperationResource::collection($this->whenLoaded('operations')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
