<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RequestOwnAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private const APPOINTMENT_START_TIME = '2026-04-25 09:00:00';
    private const APPOINTMENT_END_TIME = '2026-04-25 09:30:00';
    private const APPOINTMENT_NOTES = 'Returning patient requests a follow-up consultation.';

    public function test_authenticated_patient_can_submit_pending_appointment_request(): void
    {
        $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $user = $this->createUser('patient');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/appointments/request', [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
            'notes' => self::APPOINTMENT_NOTES,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Appointment request submitted successfully.')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.appointment_type_id', $appointmentTypeId)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.notes', self::APPOINTMENT_NOTES);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
            'status' => 'pending',
            'notes' => self::APPOINTMENT_NOTES,
        ]);

        $appointmentId = (int) DB::table('appointments')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertDatabaseHas('appointment_slot_locks', [
            'appointment_id' => $appointmentId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
        ]);
    }

    public function test_authenticated_patient_cannot_submit_overlapping_appointment_request(): void
    {
        $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $user = $this->createUser('patient');

        DB::table('appointments')->insert([
            'user_id' => $user->id,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/appointments/request', [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Selected time slot is not available.');
    }

    private function createRole(string $name): int
    {
        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
        ]);
    }

    private function createAppointmentType(): int
    {
        return (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Follow-up Consultation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $roleName): User
    {
        $roleId = (int) DB::table('roles')->where('name', $roleName)->value('id');

        return User::factory()->create([
            'role_id' => $roleId,
        ]);
    }
}
