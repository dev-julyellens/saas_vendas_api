<?php

declare(strict_types=1);

namespace App\Modules\Sale\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Sale\Models\SaleItem;

class SaleItemRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new SaleItem);
    }
}
