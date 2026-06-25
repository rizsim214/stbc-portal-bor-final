<?php

namespace App\Modules\Users\DTOs;

final readonly class AdminRegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int $roleId,
        public ?string $subRole,
    ) {
    }
}
