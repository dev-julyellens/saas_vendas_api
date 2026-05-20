<?php

namespace App\Modules\Consignment\Models;

use App\Core\Models\BaseModel;

class ConsignmentStock extends BaseModel
{
    protected $table = 'consignment_stocks';

    protected $fillable = [
        'company_id',
        'reseller_id',
        'product_id',
        'quantity',
        'quantity_sold',
        'quantity_returned',
        'consigned_at',
        'expected_return_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'consigned_at' => 'date',
            'expected_return_at' => 'date',
        ];
    }
}
