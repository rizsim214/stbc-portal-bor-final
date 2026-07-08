<?php

namespace App\Modules\Appointments\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AppointmentSlotLockService
{
    public function syncForAppointment(Appointment $appointment, string $startTime, string $endTime): void
    {
        $now = now();

        $updated = DB::table('appointment_slot_locks')
            ->where('appointment_id', $appointment->id)
            ->update([
                'start_time' => $startTime,
                'end_time' => $endTime,
                'updated_at' => $now,
            ]);

        if ($updated > 0) {
            return;
        }

        DB::table('appointment_slot_locks')->insert([
            'appointment_id' => $appointment->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function releaseForAppointment(int $appointmentId): void
    {
        DB::table('appointment_slot_locks')
            ->where('appointment_id', $appointmentId)
            ->delete();
    }
}
