<?php

namespace App\Modules\Sale\Models;

use App\Core\Models\BaseLineModel;

class SaleItem extends BaseLineModel
{
    protected $fillable = [
        'company_id',
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
