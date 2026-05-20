<?php

namespace App\Core\Audit\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelAudited
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Model $auditable,
        public string $event,
        public array $oldValues,
        public array $newValues,
    )
    {
    }
}
