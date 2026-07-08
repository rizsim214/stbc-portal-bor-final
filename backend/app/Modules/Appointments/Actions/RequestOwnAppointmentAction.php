<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\RequestOwnAppointmentDTO;
use App\Modules\Appointments\Services\AppointmentScheduleService;
use App\Modules\Appointments\Services\AppointmentSlotLockService;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RequestOwnAppointmentAction
{
    public function __construct(
        private readonly AppointmentScheduleService $appointmentScheduleService,
        private readonly AppointmentSlotLockService $appointmentSlotLockService,
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

        try {
            $appointment = DB::transaction(function () use ($dto): Appointment {
                $appointment = Appointment::query()->create([
                    'user_id' => $dto->userId,
                    'appointment_type_id' => $dto->appointmentTypeId,
                    'start_time' => $dto->startTime,
                    'end_time' => $dto->endTime,
                    'status' => 'pending',
                    'notes' => $dto->notes,
                ]);

                $this->appointmentSlotLockService->syncForAppointment(
                    $appointment,
                    $dto->startTime,
                    $dto->endTime,
                );

                return $appointment->load('type');
            });
        } catch (QueryException $e) {
            if (in_array($e->getCode(), ['23P01', '23505'], true)) {
                throw new UnprocessableEntityApiException(
                    message: 'Selected time slot is not available.',
                    errorCode: 'APPOINTMENT_SLOT_UNAVAILABLE',
                );
            }

            throw $e;
        }

        $this->lifecycleDispatcher->dispatch($appointment, 'created');

        return $appointment;
    }
}
