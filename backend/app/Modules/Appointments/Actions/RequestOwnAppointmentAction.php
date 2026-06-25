<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\RequestOwnAppointmentDTO;
use App\Modules\Appointments\Services\AppointmentScheduleService;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Support\Facades\DB;

class RequestOwnAppointmentAction
{
    public function __construct(
        private readonly AppointmentScheduleService $appointmentScheduleService,
        private readonly AppointmentLifecycleDispatcher $lifecycleDispatcher,
    ) {
    }

    public function execute(RequestOwnAppointmentDTO $dto): Appointment
    {
        if (!$this->appointmentScheduleService->isValidSlotWindow($dto->startTime, $dto->endTime)) {
            throw new UnprocessableEntityApiException(
                message: 'Selected time slot is outside clinic availability.',
                errorCode: 'APPOINTMENT_SLOT_INVALID',
            );
        }

        if (!$this->appointmentScheduleService->isSlotAvailable($dto->startTime, $dto->endTime)) {
            throw new UnprocessableEntityApiException(
                message: 'Selected time slot is not available.',
                errorCode: 'APPOINTMENT_SLOT_UNAVAILABLE',
            );
        }

        $appointment = DB::transaction(function () use ($dto): Appointment {
            return Appointment::query()->create([
                'user_id' => $dto->userId,
                'appointment_type_id' => $dto->appointmentTypeId,
                'start_time' => $dto->startTime,
                'end_time' => $dto->endTime,
                'status' => 'pending',
                'notes' => $dto->notes,
            ])->load('type');
        });

        $this->lifecycleDispatcher->dispatch($appointment, 'created');

        return $appointment;
    }
}
