<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permissões por tenant (company_id) — seed replicado em cada empresa.
 */
class Permission extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'module',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withPivot('company_id')
            ->withTimestamps();
    }
}
