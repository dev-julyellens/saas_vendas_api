<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Models;

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Models\Concerns\BelongsToCompany;
use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\StockMovement;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Registro imutável de operação — sem soft delete (trilha de auditoria).
 */
class ConsignmentOperation extends Model
{
    use BelongsToCompany;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'consignment_id',
        'consignment_item_id',
        'product_id',
        'operation_type',
        'quantity',
        'notes',
        'metadata',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'operation_type' => ConsignmentOperationType::class,
            'quantity' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ConsignmentItem::class, 'consignment_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
