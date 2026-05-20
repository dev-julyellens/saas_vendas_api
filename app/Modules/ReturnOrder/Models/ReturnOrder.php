<?php

declare(strict_types=1);

namespace App\Modules\ReturnOrder\Models;

use App\Core\Enums\ReturnStatus;
use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Core\Models\Concerns\HasStockMovements;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Devolução de mercadoria — tabela `returns` (Return é palavra reservada em PHP). */
class ReturnOrder extends BaseModel
{
    use HasFactory;
    use HasStockMovements;

    protected $table = 'returns';

    protected $fillable = [
        'company_id',
        'reseller_id',
        'sale_id',
        'consignment_id',
        'code',
        'status',
        'reason',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReturnStatus::class,
            'returned_at' => 'datetime',
        ];
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
