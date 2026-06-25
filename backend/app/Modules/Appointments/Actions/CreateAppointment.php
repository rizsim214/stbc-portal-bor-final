<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\DTOs\StoreAppointmentDTO;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Scheduling\Services\SchedulingService;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreateAppointment
{
    public function __construct(
        private readonly SchedulingService $schedulingService,
        private readonly AppointmentLifecycleDispatcher $lifecycleDispatcher,
    ) {
    }

    public function execute(StoreAppointmentDTO $dto): Model
    {
        try {
            $appointment = DB::transaction(fn() => $this->createWithinTransaction($dto));
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

    public function createWithinTransaction(StoreAppointmentDTO $dto): Model
    {
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

        $bookingRows = array_map(
            fn(int $resourceId): array => [
                'resource_id' => $resourceId,
                'appointment_id' => $appointment->id,
                'start_time' => $dto->startTime,
                'end_time' => $dto->endTime,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $dto->resourceIds
        );

        DB::table('resource_bookings')->insert($bookingRows);

        return $appointment->load(['resources', 'type']);
    }
}
