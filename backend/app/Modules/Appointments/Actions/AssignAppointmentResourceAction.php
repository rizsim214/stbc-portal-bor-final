<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Models\Resource;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Scheduling\Services\SchedulingService;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Support\Facades\DB;

class AssignAppointmentResourceAction
{
    public function __construct(
        private readonly SchedulingService $schedulingService,
        private readonly AppointmentLifecycleDispatcher $lifecycleDispatcher,
    ) {
    }

    public function execute(Appointment $appointment, int $resourceId): Appointment
    {
        $resource = Resource::query()->findOrFail($resourceId);

        if (!$resource->is_available) {
            throw new UnprocessableEntityApiException(
                message: 'Selected staff member is currently marked unavailable.',
                errorCode: 'APPOINTMENT_RESOURCE_MARKED_UNAVAILABLE',
            );
        }

        if (
            !$this->schedulingService->isAvailableExcludingAppointment(
                [$resource->id],
                $appointment->start_time,
                $appointment->end_time,
                $appointment->id,
            )
        ) {
            throw new UnprocessableEntityApiException(
                message: 'Selected staff member is not available for this appointment.',
                errorCode: 'APPOINTMENT_RESOURCE_UNAVAILABLE',
            );
        }

        $updatedAppointment = DB::transaction(function () use ($appointment, $resource): Appointment {
            DB::table('resource_bookings')
                ->where('appointment_id', $appointment->id)
                ->delete();

            $appointment->resources()->sync([$resource->id]);

            DB::table('resource_bookings')->insert([
                'resource_id' => $resource->id,
                'appointment_id' => $appointment->id,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $appointment->status = 'assigned';
            $appointment->save();

            return $appointment->load([
                'user:id,name,email',
                'type:id,name,description',
                'resources:id,name,type',
            ]);
        });

        $this->lifecycleDispatcher->dispatch($updatedAppointment, 'assigned');

        return $updatedAppointment;
    }
}
