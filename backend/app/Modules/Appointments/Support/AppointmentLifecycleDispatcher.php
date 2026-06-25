<?php

namespace App\Modules\Appointments\Support;

use App\Models\Appointment;
use App\Modules\Appointments\Events\AppointmentCalendarUpdated;
use App\Modules\Appointments\Events\AppointmentLifecycleUpdated;
use App\Modules\Appointments\Jobs\SendAppointmentLifecycleNotifications;

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
        SendAppointmentLifecycleNotifications::dispatch($fresh->id, $action)->afterCommit();
    }
}
