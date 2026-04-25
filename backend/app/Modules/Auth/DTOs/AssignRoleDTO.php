<?php

namespace App\Modules\Auth\DTOs;

final readonly class AssignRoleDTO
{
    public function __construct(
        public int $userId,
        public int $roleId,
    ) {
    }
}

