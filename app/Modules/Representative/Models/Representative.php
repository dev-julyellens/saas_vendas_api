<?php

namespace App\Modules\Representative\Models;

use App\Core\Models\BaseModel;

class Representative extends BaseModel
{
    protected $fillable = [
        'company_id',
        'name',
        'document',
        'email',
        'phone',
        'commission_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
