<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\StoreAppointmentDTO;
use App\Modules\Scheduling\Services\SchedulingService;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateAppointment
{
    public function __construct(
        private readonly SchedulingService $schedulingService,
    ) {
    }

    public function execute(StoreAppointmentDTO $dto): Model
    {
        return DB::transaction(function () use ($dto) {
            if (
                !$this->schedulingService->isAvailable(
                    $dto->resourceIds,
                    $dto->startTime,
                    $dto->endTime
                )
            ) {
                throw new UnprocessableEntityApiException(
                    message: 'Selected time slot is not available.',
                    errorCode: 'APPOINTMENT_SLOT_UNAVAILABLE',
                );
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
