<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Auth\Concerns\LogsAuthenticationAccess;
use App\Core\Enums\AccessLogEvent;
use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use App\Modules\Auth\DTOs\ResetPasswordDTO;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    use LogsAuthenticationAccess;

    public function __construct(private UserRepository $users)
    {
    }

    public function sendResetLink(ForgotPasswordDTO $dto): void
    {
        $user = $this->users->findByEmail($dto->email);

        if ($user !== null)
        {
            Password::broker('users')->sendResetLink(['email' => $dto->email]);
            $this->logAccess(AccessLogEvent::PasswordResetRequested, $user);
        }

        // Resposta sempre genérica — não revelar se e-mail existe
    }

    public function reset(ResetPasswordDTO $dto): void
    {
        $status = Password::broker('users')->reset(
            [
                'email' => $dto->email,
                'password' => $dto->password,
                'password_confirmation' => $dto->passwordConfirmation,
                'token' => $dto->token,
            ],
            function ($user, string $password): void
            {
                $user->forceFill(['password' => $password])->save();
                $this->logAccess(AccessLogEvent::PasswordResetCompleted, $user);
            }
        );

        if ($status !== Password::PASSWORD_RESET)
        {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
