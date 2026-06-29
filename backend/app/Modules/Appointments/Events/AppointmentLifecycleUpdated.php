<?php

namespace App\Modules\Appointments\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class AppointmentLifecycleUpdated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly string $action,
        private readonly Appointment $appointment,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.'.$this->appointment->user_id.'.appointments'),
            new PrivateChannel('admin.appointments'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'appointment.lifecycle.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'appointment' => $this->appointment->load([
                'user:id,name,email',
                'type:id,name,description',
                'resources:id,name,type',
            ])->toArray(),
            'occurred_at' => now()->toIso8601String(),
        ];
    }
}
