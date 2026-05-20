<?php

declare(strict_types=1);

namespace App\Modules\Commission\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'representative_id' => $this->representative_id,
            'base_amount' => (float) $this->base_amount,
            'rate' => (float) $this->rate,
            'amount' => (float) $this->amount,
            'status' => $this->status->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'representative' => $this->whenLoaded('representative', fn() => [
                'id' => $this->representative->id,
                'name' => $this->representative->name,
            ]),
            'sale' => $this->whenLoaded('sale', fn() => [
                'id' => $this->sale->id,
                'code' => $this->sale->code,
                'total' => (float) $this->sale->total,
                'reseller' => $this->sale->relationLoaded('reseller') && $this->sale->reseller ? [
                    'id' => $this->sale->reseller->id,
                    'name' => $this->sale->reseller->name,
                ] : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
