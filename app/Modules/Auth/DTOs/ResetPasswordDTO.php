<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class ResetPasswordDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
        public string $passwordConfirmation,
    )
    {
    }
}
