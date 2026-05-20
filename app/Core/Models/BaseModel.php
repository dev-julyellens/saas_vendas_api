<?php

namespace App\Core\Models;

use App\Core\Models\Concerns\Auditable;
use App\Core\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model base tenant-aware com UUID, soft delete e auditoria.
 *
 * UUID: facilita integração externa e evita enumeração sequencial entre tenants.
 * SoftDeletes: recuperação de dados em operações de consignação/financeiro.
 */
abstract class BaseModel extends Model
{
    use Auditable;
    use BelongsToCompany;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
