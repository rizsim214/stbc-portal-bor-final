<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\UpdateAppointmentDTO;
use App\Modules\Appointments\Services\AppointmentScheduleService;
use App\Modules\Appointments\Services\AppointmentSlotLockService;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Scheduling\Services\SchedulingService;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UpdateAppointmentAction
{
    public function __construct(
        private readonly AppointmentScheduleService $appointmentScheduleService,
        private readonly AppointmentSlotLockService $appointmentSlotLockService,
        private readonly SchedulingService $schedulingService,
        private readonly AppointmentLifecycleDispatcher $lifecycleDispatcher,
    ) {
    }

    public function execute(Appointment $appointment, UpdateAppointmentDTO $dto): Appointment
    {
        if (!$this->appointmentScheduleService->isValidSlotWindow($dto->startTime, $dto->endTime)) {
            throw new UnprocessableEntityApiException(
                message: 'Selected time slot is outside clinic availability.',
                errorCode: 'APPOINTMENT_SLOT_INVALID',
            );
        }

        if (
            !$this->appointmentScheduleService->isSlotAvailable(
                $dto->startTime,
                $dto->endTime,
                $appointment->id,
            )
        ) {
            throw new UnprocessableEntityApiException(
                message: 'Selected time slot is not available.',
                errorCode: 'APPOINTMENT_SLOT_UNAVAILABLE',
            );
        }

        $resourceIds = $appointment->resources()->pluck('resources.id')->map(
            static fn (mixed $resourceId): int => (int) $resourceId
        )->all();

        if (
            $resourceIds !== []
            && !$this->schedulingService->isAvailableExcludingAppointment(
                $resourceIds,
                $dto->startTime,
                $dto->endTime,
                $appointment->id,
            )
        ) {
            throw new UnprocessableEntityApiException(
                message: 'Assigned staff member is not available for the selected schedule.',
                errorCode: 'APPOINTMENT_RESOURCE_UNAVAILABLE',
            );
        }

        try {
            $updatedAppointment = DB::transaction(function () use ($appointment, $dto, $resourceIds): Appointment {
                $appointment->update([
                    'appointment_type_id' => $dto->appointmentTypeId,
                    'start_time' => $dto->startTime,
                    'end_time' => $dto->endTime,
                    'notes' => $dto->notes,
                ]);

                if ($resourceIds !== []) {
                    DB::table('resource_bookings')
                        ->where('appointment_id', $appointment->id)
                        ->update([
                            'start_time' => $dto->startTime,
                            'end_time' => $dto->endTime,
                            'updated_at' => now(),
                        ]);
                } else {
                    $this->appointmentSlotLockService->syncForAppointment(
                        $appointment,
                        $dto->startTime,
                        $dto->endTime,
                    );
                }

                return $appointment->load([
                    'user:id,name,email',
                    'type:id,name,description',
                    'resources:id,name,type',
                ]);
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

        $this->lifecycleDispatcher->dispatch($updatedAppointment, 'updated');

        return $updatedAppointment;
    }
}
