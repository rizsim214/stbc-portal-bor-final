<?php

namespace App\Modules\Auth\DTOs;

final readonly class UpdateStaffStatusDTO
{
    public function __construct(
        public int $userId,
        public string $staffStatus,
    ) {
    }
}

