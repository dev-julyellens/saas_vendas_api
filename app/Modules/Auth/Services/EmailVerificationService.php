<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Auth\Concerns\LogsAuthenticationAccess;
use App\Core\Enums\AccessLogEvent;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationService
{
    use LogsAuthenticationAccess;

    public function sendNotification(User $user): void
    {
        if ($user->hasVerifiedEmail())
        {
            return;
        }

        $user->sendEmailVerificationNotification();
        $this->logAccess(AccessLogEvent::EmailVerificationSent, $user);
    }

    public function verifySignedRequest(EmailVerificationRequest $request): void
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail())
        {
            return;
        }

        if ($user->markEmailAsVerified())
        {
            event(new Verified($user));
            $this->logAccess(AccessLogEvent::EmailVerified, $user);
        }
    }

    public function verifyFromLink(Request $request, string $id, string $hash): bool
    {
        $user = User::query()->withoutGlobalScopes()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash))
        {
            return false;
        }

        if (! URL::hasValidSignature($request))
        {
            return false;
        }

        if ($user->hasVerifiedEmail())
        {
            return true;
        }

        if ($user->markEmailAsVerified())
        {
            event(new Verified($user));
            $this->logAccess(AccessLogEvent::EmailVerified, $user);
        }

        return true;
    }
}
