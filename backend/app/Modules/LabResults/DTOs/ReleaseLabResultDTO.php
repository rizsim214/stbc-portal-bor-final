<?php

namespace App\Modules\LabResults\DTOs;

final readonly class ReleaseLabResultDTO
{
    public function __construct(
        public int $labResultId,
        public ?string $releasedAt = null,
    ) {
    }
}

