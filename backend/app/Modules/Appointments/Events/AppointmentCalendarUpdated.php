<?php

namespace App\Modules\Appointments\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class AppointmentCalendarUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly string $action,
        private readonly Appointment $appointment,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('appointments.calendar'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'appointment.calendar.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'appointment' => [
                'id' => $this->appointment->id,
                'appointment_type_id' => $this->appointment->appointment_type_id,
                'start_time' => $this->appointment->start_time,
                'end_time' => $this->appointment->end_time,
                'status' => $this->appointment->status,
            ],
            'occurred_at' => now()->toIso8601String(),
        ];
    }
}
