<?php

namespace App\Modules\Appointments\Services;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\AvailabilityDTO;

class AvailabilityService
{
    public function isAvailable(array $resourceIds, string $start, string $end): bool
    {
        return !Appointment::whereHas('resources', function ($q) use ($resourceIds) {
            $q->whereIn('resources.id', $resourceIds);
        })
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            })
            ->exists();
    }

    public function getAvailableSlots(AvailabilityDTO $dto): array
    {
        return [
            'message' => 'Implement slot generation logic here',
            'date' => $dto->date,
            'appointment_type_id' => $dto->appointmentTypeId,
        ];
    }
}

