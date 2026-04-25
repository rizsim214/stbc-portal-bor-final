<?php

namespace App\Modules\Auth\DTOs;

final readonly class AdminRegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int $roleId,
        public ?string $staffStatus = null,
    ) {
    }
}

