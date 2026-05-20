<?php

declare(strict_types=1);

namespace App\Modules\Commission\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Commission\Models\Commission;

class CommissionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Commission);
    }
}
