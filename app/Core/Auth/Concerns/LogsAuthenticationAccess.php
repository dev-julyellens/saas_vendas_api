<?php

declare(strict_types=1);

namespace App\Core\Auth\Concerns;

use App\Core\Enums\AccessLogEvent;
use App\Models\User;
use App\Modules\Auth\Models\AccessLog;
use Illuminate\Http\Request;

trait LogsAuthenticationAccess
{
    protected function logAccess(
        AccessLogEvent $event,
        ?User $user = null,
        ?string $email = null,
        ?array $metadata = null,
    ): void
    {
        $request = request();

        AccessLog::query()->create([
            'company_id' => $user?->company_id,
            'user_id' => $user?->id,
            'email' => $email ?? $user?->email,
            'event' => $event,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? $request->userAgent() : null,
            'metadata' => $metadata,
        ]);
    }
}
