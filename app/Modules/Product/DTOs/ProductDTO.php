<?php

namespace App\Modules\Product\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class ProductDTO extends BaseDTO
{
    public function __construct(
        public string $sku,
        public string $name,
        public ?string $description,
        public string $unit_price,
        public ?string $cost_price = null,
        public bool $is_active = true,
    )
    {
    }
}
