<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Core\DTOs\BaseDTO;

readonly class ForgotPasswordDTO extends BaseDTO
{
    public function __construct(public string $email)
    {
    }
}
