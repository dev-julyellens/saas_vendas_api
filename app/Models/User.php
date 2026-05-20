<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Models\Concerns\BelongsToCompany;
use App\Modules\Company\Models\Company;
use App\Modules\Rbac\Models\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * Usuário autenticável — JWT stateless (API ONLY).
 * is_master: admin da plataforma com acesso irrestrito.
 */
class User extends Authenticatable implements JWTSubject
{
    use BelongsToCompany;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'is_master',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_master' => 'boolean',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'company_id' => $this->company_id,
            'is_master' => $this->is_master,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('company_id')
            ->withTimestamps();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->is_master)
        {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('slug', $slug))
            ->exists();
    }

    public function hasRole(string $slug): bool
    {
        if ($this->is_master)
        {
            return true;
        }

        return $this->roles()->where('slug', $slug)->exists();
    }
}
