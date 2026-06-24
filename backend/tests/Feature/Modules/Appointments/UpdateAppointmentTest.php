<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private const MEDICAL_CHECKUP = 'Medical Check-up';
    private const APPOINTMENT_URL = '/api/appointments/';
    private const START_TIME1 = '2026-06-24 10:00:00';
    private const END_TIME1 = '2026-06-24 10:30:00';
    private const START_TIME2 = '2026-06-24 11:00:00';
    private const END_TIME2 = '2026-06-24 11:30:00';
    private const START_TIME3 = '2026-06-24 09:00:00';
    private const END_TIME3 = '2026-06-24 09:30:00';
    private const NOTES = 'Updated request for chest x-ray screening.';

    public function test_patient_can_update_their_own_appointment(): void
    {
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType(self::MEDICAL_CHECKUP);
        $updatedTypeId = $this->createAppointmentType('Chest X-Ray');
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId);

        Sanctum::actingAs($patient);

        $this->patchJson(self::APPOINTMENT_URL . $appointmentId, [
            'appointment_type_id' => $updatedTypeId,
            'start_time' => self::START_TIME1,
            'end_time' => self::END_TIME1,
            'notes' => self::NOTES,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Appointment updated successfully.')
            ->assertJsonPath('data.appointment_type_id', $updatedTypeId)
            ->assertJsonPath('data.notes', self::NOTES);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentId,
            'appointment_type_id' => $updatedTypeId,
            'start_time' => self::START_TIME1,
            'end_time' => self::END_TIME1,
            'notes' => self::NOTES,
        ]);
    }

    public function test_update_rejects_overlapping_slot(): void
    {
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType(self::MEDICAL_CHECKUP);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, self::START_TIME3, self::END_TIME3);
        $otherPatient = User::factory()->create(['role_id' => $patientRoleId]);
        $this->createAppointment($otherPatient->id, $appointmentTypeId, self::START_TIME1, self::END_TIME1);

        Sanctum::actingAs($patient);

        $this->patchJson(self::APPOINTMENT_URL . $appointmentId, [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::START_TIME1,
            'end_time' => self::END_TIME1,
            'notes' => 'Conflicting update',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Selected time slot is not available.');
    }

    public function test_admin_update_also_moves_assigned_resource_booking(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType(self::MEDICAL_CHECKUP);
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId);
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 3, '07:30:00', '17:00:00');
        $this->assignResource($appointmentId, $resourceId, self::START_TIME3, self::END_TIME3);

        Sanctum::actingAs($admin);

        $this->patchJson(self::APPOINTMENT_URL . $appointmentId, [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::START_TIME2,
            'end_time' => self::END_TIME2,
            'notes' => 'Rescheduled by admin.',
        ])
            ->assertOk()
            ->assertJsonPath('data.start_time', self::START_TIME2);

        $this->assertDatabaseHas('resource_bookings', [
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
            'start_time' => self::START_TIME2,
            'end_time' => '2026-06-24 11:30:00',
        ]);
    }

    private function createRole(string $name): int
    {
        $existingId = DB::table('roles')->where('name', $name)->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        return (int) DB::table('roles')->insertGetId(['name' => $name]);
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
        int $userId,
        int $appointmentTypeId,
        string $startTime = self::START_TIME3,
        string $endTime = self::END_TIME3,
    ): int {
        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createResource(): int
    {
        return (int) DB::table('resources')->insertGetId([
            'name' => 'Dr. Schedule',
            'type' => 'doctor',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createResourceSchedule(int $resourceId, int $dayOfWeek, string $startTime, string $endTime): void
    {
        DB::table('resource_schedules')->insert([
            'resource_id' => $resourceId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assignResource(int $appointmentId, int $resourceId, string $startTime, string $endTime): void
    {
        DB::table('appointment_resources')->insert([
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
        ]);

        DB::table('resource_bookings')->insert([
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
