<?php

namespace App\Modules\ReturnOrder\Models;

use App\Core\Models\BaseLineModel;

class ReturnItem extends BaseLineModel
{
    protected $fillable = [
        'company_id',
        'return_order_id',
        'product_id',
        'quantity',
    ];
}
