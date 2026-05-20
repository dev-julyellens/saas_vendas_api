<?php

namespace App\Modules\Product\Models;

use App\Core\Models\BaseModel;

class Product extends BaseModel
{
    protected $fillable = [
        'company_id',
        'sku',
        'name',
        'description',
        'unit_price',
        'cost_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
