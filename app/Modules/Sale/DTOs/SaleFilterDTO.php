<?php

declare(strict_types=1);

namespace App\Modules\Sale\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class SaleFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $status = null,
        public ?string $reseller_id = null,
        public ?string $customer_id = null,
        public ?string $representative_id = null,
        public ?string $consignment_id = null,
        public ?string $code = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?float $min_total = null,
        public ?float $max_total = null,
        public bool $confirmed_only = false,
    )
    {
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn($v) => $v !== null && $v !== false);
    }
}
