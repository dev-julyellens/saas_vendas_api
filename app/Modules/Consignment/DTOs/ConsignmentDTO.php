<?php

declare(strict_types=1);

namespace App\Modules\Consignment\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class ConsignmentDTO extends BaseDTO
{
    /**
     * @param  list<array{product_id: string, quantity: int, unit_price?: float|null}>  $items
     */
    public function __construct(
        public string $reseller_id,
        public ?string $representative_id,
        public string $consigned_at,
        public ?string $expected_return_at,
        public ?string $notes,
        public array $items,
    )
    {
    }
}
