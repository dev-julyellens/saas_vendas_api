<?php

namespace App\Modules\Commission\Models;

use App\Core\Models\BaseModel;

class Commission extends BaseModel
{
    protected $fillable = [
        'company_id',
        'sale_id',
        'representative_id',
        'base_amount',
        'rate',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'rate' => 'decimal:4',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }
}
