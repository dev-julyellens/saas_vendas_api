<?php

namespace App\Modules\Product\Services;

use App\Core\Services\BaseService;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    public function __construct(ProductRepository $repository)
    {
        parent::__construct($repository);
    }

    public function store(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto)
        {
            $product = $this->repository->create($dto->toArray());
            event(new ProductCreated($product));

            return $product;
        });
    }
}
