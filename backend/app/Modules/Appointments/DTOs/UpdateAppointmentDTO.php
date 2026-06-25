<?php

namespace App\Modules\Appointments\DTOs;

final readonly class UpdateAppointmentDTO
{
    public function __construct(
        public int $appointmentTypeId,
        public string $startTime,
        public string $endTime,
        public ?string $notes,
    ) {
    }
}
