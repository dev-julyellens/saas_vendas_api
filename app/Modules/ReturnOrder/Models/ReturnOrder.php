<?php

namespace App\Modules\ReturnOrder\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnOrder extends BaseModel
{
    protected $table = 'return_orders';

    protected $fillable = [
        'company_id',
        'reseller_id',
        'sale_id',
        'code',
        'status',
        'reason',
        'returned_at',
    ];

    protected function casts(): array
    {
        return ['returned_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_order_id');
    }
}
