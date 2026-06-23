<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListMyAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_patient_can_list_only_their_appointments(): void
    {
        $patientRoleId = $this->createRole('patient');
        $otherRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $otherPatient = User::factory()->create(['role_id' => $otherRoleId]);

        $ownAppointmentId = $this->createAppointment($patient->id, $appointmentTypeId, '2026-04-25 09:00:00');
        $this->createAppointment($otherPatient->id, $appointmentTypeId, '2026-04-26 09:00:00');

        Sanctum::actingAs($patient);

        $response = $this->getJson('/api/appointments/mine');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownAppointmentId)
            ->assertJsonPath('data.0.user_id', $patient->id)
            ->assertJsonPath('data.0.type.name', 'Medical Check-up')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 1);
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

    private function createAppointment(int $userId, int $appointmentTypeId, string $startTime): int
    {
        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $startTime,
            'end_time' => date('Y-m-d H:i:s', strtotime($startTime . ' +30 minutes')),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
