<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Events;

use App\Modules\Consignment\Models\Consignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsignmentDispatched
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Consignment $consignment)
    {
    }
}
