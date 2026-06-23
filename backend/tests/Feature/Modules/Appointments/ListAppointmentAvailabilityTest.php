<?php

namespace Tests\Feature\Modules\Appointments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListAppointmentAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_lists_only_open_slots_for_the_day(): void
    {
        $appointmentTypeId = $this->createAppointmentType();
        $this->createAppointment('2026-06-24 09:00:00', '2026-06-24 09:30:00');
        $this->createAppointment('2026-06-24 10:00:00', '2026-06-24 10:30:00');

        $this->getJson('/api/appointments/availability?date=2026-06-24&appointment_type_id=' . $appointmentTypeId)
            ->assertOk()
            ->assertJsonPath('date', '2026-06-24')
            ->assertJsonPath('appointment_type_id', $appointmentTypeId)
            ->assertJsonMissing([
                'start_time' => '2026-06-24 09:00:00',
                'end_time' => '2026-06-24 09:30:00',
            ])
            ->assertJsonMissing([
                'start_time' => '2026-06-24 10:00:00',
                'end_time' => '2026-06-24 10:30:00',
            ])
            ->assertJsonFragment([
                'start_time' => '2026-06-24 09:30:00',
                'end_time' => '2026-06-24 10:00:00',
            ]);
    }

    public function test_availability_can_keep_current_slot_when_editing_existing_appointment(): void
    {
        $appointmentTypeId = $this->createAppointmentType();
        $appointmentId = $this->createAppointment('2026-06-24 09:00:00', '2026-06-24 09:30:00');

        $this->getJson('/api/appointments/availability?date=2026-06-24&appointment_type_id=' . $appointmentTypeId . '&appointment_id=' . $appointmentId)
            ->assertOk()
            ->assertJsonFragment([
                'start_time' => '2026-06-24 09:00:00',
                'end_time' => '2026-06-24 09:30:00',
            ]);
    }

    private function createAppointmentType(): int
    {
        return (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Medical Check-up',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(string $startTime, string $endTime): int
    {
        $patientRoleId = (int) (DB::table('roles')->where('name', 'patient')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'patient']));
        $userId = (int) DB::table('users')->insertGetId([
            'name' => 'Test Patient',
            'email' => uniqid('patient', true) . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $patientRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $this->createAppointmentType(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
