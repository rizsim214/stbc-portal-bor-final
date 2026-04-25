<?php

namespace App\Modules\Scheduling\DTOs;

final readonly class SchedulingAvailabilityDTO
{
    /**
     * @param  array<int>  $resourceIds
     */
    public function __construct(
        public string $date,
        public int $appointmentTypeId,
        public array $resourceIds,
    ) {
    }
}

