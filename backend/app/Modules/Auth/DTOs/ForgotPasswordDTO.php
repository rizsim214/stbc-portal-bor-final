<?php

namespace App\Modules\Auth\DTOs;

final readonly class ForgotPasswordDTO
{
    public function __construct(
        public string $email,
    ) {
    }
}

