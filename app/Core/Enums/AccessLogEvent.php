<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum AccessLogEvent: string
{
    case LoginSuccess = 'login_success';
    case LoginFailed = 'login_failed';
    case LoginLocked = 'login_locked';
    case Logout = 'logout';
    case TokenRefreshed = 'token_refreshed';
    case PasswordResetRequested = 'password_reset_requested';
    case PasswordResetCompleted = 'password_reset_completed';
    case EmailVerificationSent = 'email_verification_sent';
    case EmailVerified = 'email_verified';
    case SessionRevoked = 'session_revoked';
}
