<?php

namespace App\Modules\Users\DTOs;

final readonly class UpdateStaffStatusDTO
{
    public function __construct(
        public int $userId,
        public string $staffStatus,
    ) {
    }
}


