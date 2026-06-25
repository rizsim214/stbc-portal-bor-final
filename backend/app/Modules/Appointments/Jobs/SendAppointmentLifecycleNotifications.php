<?php

namespace App\Modules\Appointments\Jobs;

use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAppointmentLifecycleNotifications implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $action,
    ) {
    }

    public function handle(): void
    {
        $appointment = Appointment::query()->with(['user', 'type', 'resources'])->find($this->appointmentId);
        if (!$appointment) {
            return;
        }

        Log::info('appointment.lifecycle', [
            'action' => $this->action,
            'appointment_id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'status' => $appointment->status,
        ]);

        // Later: Mail / SMS / notification dispatch goes here.
    }
}
