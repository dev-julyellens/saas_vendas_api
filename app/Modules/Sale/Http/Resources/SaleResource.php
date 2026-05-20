<?php

declare(strict_types=1);

namespace App\Modules\Sale\Http\Resources;

use App\Modules\Commission\Http\Resources\CommissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'reseller_id' => $this->reseller_id,
            'customer_id' => $this->customer_id,
            'representative_id' => $this->representative_id,
            'consignment_id' => $this->consignment_id,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'sold_at' => $this->sold_at?->toIso8601String(),
            'reseller' => $this->whenLoaded('reseller', fn() => [
                'id' => $this->reseller->id,
                'name' => $this->reseller->name,
            ]),
            'customer' => $this->whenLoaded('customer', fn() => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ] : null),
            'representative' => $this->whenLoaded('representative', fn() => $this->representative ? [
                'id' => $this->representative->id,
                'name' => $this->representative->name,
            ] : null),
            'consignment' => $this->whenLoaded('consignment', fn() => $this->consignment ? [
                'id' => $this->consignment->id,
                'code' => $this->consignment->code,
            ] : null),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'commission' => $this->whenLoaded('commission', fn() => $this->commission
                ? new CommissionResource($this->commission)
                : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
