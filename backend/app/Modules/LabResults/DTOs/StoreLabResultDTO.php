<?php

namespace App\Modules\LabResults\DTOs;

final readonly class StoreLabResultDTO
{
    /**
     * @param  array<string, mixed>|null  $resultData
     */
    public function __construct(
        public int $appointmentId,
        public ?string $fileKey = null,
        public ?array $resultData = null,
        public ?string $releasedAt = null,
    ) {
    }
}
