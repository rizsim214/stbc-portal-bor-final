<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private const APPOINTMENT_URL = '/api/appointments/';
    public function test_patient_can_view_their_own_appointment(): void
    {
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId);

        Sanctum::actingAs($patient);

        $this->getJson(self::APPOINTMENT_URL . $appointmentId)
            ->assertOk()
            ->assertJsonPath('data.id', $appointmentId)
            ->assertJsonPath('data.user_id', $patient->id);
    }

    public function test_patient_cannot_view_another_patients_appointment(): void
    {
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $otherPatient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($otherPatient->id, $appointmentTypeId);

        Sanctum::actingAs($patient);

        $this->getJson(self::APPOINTMENT_URL . $appointmentId)
            ->assertForbidden();
    }

    public function test_admin_can_view_any_appointment(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId);

        Sanctum::actingAs($admin);

        $this->getJson(self::APPOINTMENT_URL . $appointmentId)
            ->assertOk()
            ->assertJsonPath('data.id', $appointmentId);
    }

    private function createRole(string $name): int
    {
        $existingId = DB::table('roles')->where('name', $name)->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        return (int) DB::table('roles')->insertGetId(['name' => $name]);
    }

    private function createAppointmentType(): int
    {
        return (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Medical Check-up',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(int $userId, int $appointmentTypeId): int
    {
        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => '2026-04-25 09:00:00',
            'end_time' => '2026-04-25 09:30:00',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
