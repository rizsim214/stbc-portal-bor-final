<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;

class UpdateAppointmentStatusAction
{
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['assigned'],
        'assigned' => ['checkup_ongoing'],
        'checkup_ongoing' => ['awaiting_result'],
        'awaiting_result' => ['releasing_lab_result'],
        'releasing_lab_result' => ['completed'],
        'completed' => [],
    ];

    public function __construct(
        private readonly AppointmentLifecycleDispatcher $lifecycleDispatcher,
    ) {
    }

    public function execute(Appointment $appointment, string $nextStatus): Appointment
    {
        $currentStatus = strtolower(trim((string) $appointment->status));
        $allowedStatuses = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($nextStatus, $allowedStatuses, true)) {
            throw new UnprocessableEntityApiException(
                message: 'The requested appointment status transition is not allowed.',
                errorCode: 'APPOINTMENT_STATUS_TRANSITION_INVALID',
            );
        }

        if (
            in_array($nextStatus, ['assigned', 'checkup_ongoing', 'awaiting_result', 'releasing_lab_result', 'completed'], true)
            && !$appointment->resources()->exists()
        ) {
            throw new UnprocessableEntityApiException(
                message: 'Assign a staff member before progressing this appointment.',
                errorCode: 'APPOINTMENT_REQUIRES_ASSIGNED_STAFF',
            );
        }

        $appointment->status = $nextStatus;
        $appointment->save();

        $updatedAppointment = $appointment->load([
            'user:id,name,email',
            'type:id,name,description',
            'resources:id,name,type',
        ]);

        $this->lifecycleDispatcher->dispatch($updatedAppointment, 'status_updated');

        return $updatedAppointment;
    }

    /**
     * @return array<int, string>
     */
    public static function allowedNextStatuses(string $currentStatus): array
    {
        $normalized = strtolower(trim($currentStatus));

        return self::ALLOWED_TRANSITIONS[$normalized] ?? [];
    }
}
