<?php

namespace App\Modules\Sale\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends BaseModel
{
    protected $fillable = [
        'company_id',
        'reseller_id',
        'customer_id',
        'representative_id',
        'code',
        'subtotal',
        'discount',
        'total',
        'status',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
