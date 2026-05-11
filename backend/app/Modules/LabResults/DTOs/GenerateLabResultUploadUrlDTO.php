<?php

namespace App\Modules\LabResults\DTOs;

final readonly class GenerateLabResultUploadUrlDTO
{
    public function __construct(
        public int $appointmentId,
        public string $fileName,
        public string $contentType,
        public int $sizeBytes,
    ) {
    }
}
