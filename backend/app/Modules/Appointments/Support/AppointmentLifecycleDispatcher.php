<?php

namespace App\Modules\Appointments\Support;

use App\Models\Appointment;
use App\Modules\Appointments\Events\AppointmentCalendarUpdated;
use App\Modules\Appointments\Events\AppointmentLifecycleUpdated;
use Illuminate\Support\Facades\Log;

class AppointmentLifecycleDispatcher
{
    public function dispatch(Appointment $appointment, string $action): void
    {
        $fresh = $appointment->fresh()->load([
            'user:id,name,email',
            'type:id,name,description',
            'resources:id,name,type',
        ]);

        event(new AppointmentLifecycleUpdated($action, $fresh));
        event(new AppointmentCalendarUpdated($action, $fresh));

        Log::info('appointment.lifecycle', [
            'action' => $action,
            'appointment_id' => $fresh->id,
            'user_id' => $fresh->user_id,
            'status' => $fresh->status,
        ]);
    }
}
