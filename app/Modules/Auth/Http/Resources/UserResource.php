<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'is_master' => $this->is_master,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->map(fn($r) => [
                'slug' => $r->slug,
                'name' => $r->name,
            ])),
            'permissions' => $this->when(
                $this->relationLoaded('roles'),
                fn() => $this->roles
                    ->flatMap(fn($r) => $r->permissions ?? collect())
                    ->pluck('slug')
                    ->unique()
                    ->values()
            ),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ];
    }
}
