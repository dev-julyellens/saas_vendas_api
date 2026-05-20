<?php

declare(strict_types=1);

namespace App\Modules\Consignment\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class ConsignmentItemActionDTO extends BaseDTO
{
    public function __construct(
        public string $consignment_item_id,
        public int $quantity,
        public ?string $notes = null,
    )
    {
    }
}
