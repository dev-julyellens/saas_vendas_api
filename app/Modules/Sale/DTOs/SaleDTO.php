<?php

declare(strict_types=1);

namespace App\Modules\Sale\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class SaleDTO extends BaseDTO
{
    /**
     * @param  list<array{product_id: string, quantity: int, unit_price?: float|null, consignment_item_id?: string|null}>  $items
     */
    public function __construct(
        public string $reseller_id,
        public ?string $customer_id,
        public ?string $representative_id,
        public ?string $consignment_id,
        public float $discount,
        public ?string $notes,
        public array $items,
    )
    {
    }
}
