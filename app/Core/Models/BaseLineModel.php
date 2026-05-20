<?php

namespace App\Core\Models;

use App\Core\Models\Concerns\Auditable;
use App\Core\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Itens de pedido (sale_items, return_items) — tenant-aware sem soft delete.
 */
abstract class BaseLineModel extends Model
{
    use Auditable;
    use BelongsToCompany;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];
}
