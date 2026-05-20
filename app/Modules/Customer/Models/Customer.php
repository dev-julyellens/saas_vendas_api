<?php

namespace App\Modules\Customer\Models;

use App\Core\Models\BaseModel;

class Customer extends BaseModel
{
    protected $fillable = [
        'company_id',
        'reseller_id',
        'name',
        'document',
        'email',
        'phone',
    ];
}
