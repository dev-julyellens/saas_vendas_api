<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'representative_id' => $this->representative_id,
            'name' => $this->name,
            'document' => $this->document,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'representative' => $this->whenLoaded('representative', fn() => [
                'id' => $this->representative->id,
                'name' => $this->representative->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
