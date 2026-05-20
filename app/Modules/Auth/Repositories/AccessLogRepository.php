<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Auth\Models\AccessLog;

class AccessLogRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccessLog);
    }
}
