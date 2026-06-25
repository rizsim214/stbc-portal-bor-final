<?php

namespace App\Modules\Appointments\DTOs;

final readonly class GuestBookAppointmentDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public int $appointmentTypeId,
        public string $startTime,
        public string $endTime,
        public ?string $notes,
    ) {
    }
}
