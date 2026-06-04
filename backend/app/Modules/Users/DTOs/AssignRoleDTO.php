<?php

namespace App\Modules\Users\DTOs;

final readonly class AssignRoleDTO
{
    public function __construct(
        public int $userId,
        public int $roleId,
    ) {
    }
}
