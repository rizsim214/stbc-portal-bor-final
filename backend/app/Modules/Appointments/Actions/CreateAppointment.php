<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\StoreAppointmentDTO;
use App\Modules\Appointments\Services\AvailabilityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateAppointment
{
    public function __construct(
        private readonly AvailabilityService $availabilityService,
    ) {
    }

    public function execute(StoreAppointmentDTO $dto): Model
    {
        return DB::transaction(function () use ($dto) {
            if (
                !$this->availabilityService->isAvailable(
                    $dto->resourceIds,
                    $dto->startTime,
                    $dto->endTime
                )
            ) {
                abort(422, 'Selected time slot is not available.');
            }

            $appointment = Appointment::create([
                'user_id' => $dto->userId,
                'appointment_type_id' => $dto->appointmentTypeId,
                'start_time' => $dto->startTime,
                'end_time' => $dto->endTime,
            ]);

            $appointment->resources()->attach($dto->resourceIds);

            return $appointment->load('resources');
        });
    }
}

