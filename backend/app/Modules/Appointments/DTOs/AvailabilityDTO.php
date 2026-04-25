<?php

namespace App\Modules\Appointments\DTOs;

final readonly class AvailabilityDTO
{
    public function __construct(
        public string $date,
        public int $appointmentTypeId,
    ) {
    }
}

