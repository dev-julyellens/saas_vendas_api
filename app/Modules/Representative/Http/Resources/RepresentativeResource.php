<?php

declare(strict_types=1);

namespace App\Modules\Representative\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepresentativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'commission_rate' => (float) $this->commission_rate,
            'is_active' => $this->is_active,
        ];
    }
}
