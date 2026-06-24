<?php

namespace Tests\Feature\Modules\Appointments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListAppointmentCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_lists_real_appointments_within_requested_range(): void
    {
        $appointmentTypeId = $this->createAppointmentType('Consultation');
        $includedAppointmentId = $this->createAppointment(
            $appointmentTypeId,
            '2026-06-24 09:00:00',
            '2026-06-24 09:30:00',
            'pending',
        );
        $this->createAppointment(
            $appointmentTypeId,
            '2026-06-26 13:00:00',
            '2026-06-26 13:30:00',
            'cancelled',
        );
        $this->createAppointment(
            $appointmentTypeId,
            '2026-07-03 09:00:00',
            '2026-07-03 09:30:00',
            'assigned',
        );

        $this->getJson('/api/appointments/calendar?start=2026-06-23&end=2026-06-30')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $includedAppointmentId)
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('data.0.type.name', 'Consultation');
    }

    private function createAppointmentType(string $name): int
    {
        return (int) DB::table('appointment_types')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(
        int $appointmentTypeId,
        string $startTime,
        string $endTime,
        string $status,
    ): int {
        $patientRoleId = (int) (DB::table('roles')->where('name', 'patient')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'patient']));
        $userId = (int) DB::table('users')->insertGetId([
            'name' => 'Calendar Patient',
            'email' => uniqid('calendar', true) . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $patientRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
