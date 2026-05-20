<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Models;

use App\Core\Models\BaseLineModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\SaleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentItem extends BaseLineModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'consignment_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
