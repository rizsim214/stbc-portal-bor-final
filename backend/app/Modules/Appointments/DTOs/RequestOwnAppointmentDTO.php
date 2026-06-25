<?php

namespace App\Modules\Appointments\DTOs;

final readonly class RequestOwnAppointmentDTO
{
    public function __construct(
        public int $userId,
        public int $appointmentTypeId,
        public string $startTime,
        public string $endTime,
        public ?string $notes,
    ) {
    }
}
