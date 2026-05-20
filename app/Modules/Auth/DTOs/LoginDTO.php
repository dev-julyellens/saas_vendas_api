<?php

namespace App\Modules\Auth\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $password,
    )
    {
    }
}
