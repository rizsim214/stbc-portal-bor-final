<?php

namespace App\Modules\Users\DTOs;

class UpdatePatientByAdminDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $password,
    ) {
    }
}
