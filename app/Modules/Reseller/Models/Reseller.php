<?php

namespace App\Modules\Reseller\Models;

use App\Core\Models\BaseModel;

class Reseller extends BaseModel
{
    protected $fillable = [
        'company_id',
        'representative_id',
        'name',
        'document',
        'email',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
