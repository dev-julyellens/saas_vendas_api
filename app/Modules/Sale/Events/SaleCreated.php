<?php

declare(strict_types=1);

namespace App\Modules\Sale\Events;

use App\Modules\Sale\Models\Sale;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Sale $sale)
    {
    }
}
