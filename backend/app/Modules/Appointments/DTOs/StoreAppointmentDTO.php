<?php

namespace App\Modules\Appointments\DTOs;

final readonly class StoreAppointmentDTO
{
    /**
     * @param  array<int>  $resourceIds
     */
    public function __construct(
        public int $userId,
        public int $appointmentTypeId,
        public string $startTime,
        public string $endTime,
        public array $resourceIds,
    ) {
    }
}

