<?php

namespace App\Modules\Product\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Product\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Product);
    }
}
