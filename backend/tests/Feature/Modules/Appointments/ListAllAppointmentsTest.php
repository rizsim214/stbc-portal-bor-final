<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListAllAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_appointments_for_multiple_patients(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $firstPatient = User::factory()->create(['role_id' => $patientRoleId, 'name' => 'Patient One']);
        $secondPatient = User::factory()->create(['role_id' => $patientRoleId, 'name' => 'Patient Two']);

        $this->createAppointment($firstPatient->id, $appointmentTypeId, '2026-04-25 09:00:00');
        $latestAppointmentId = $this->createAppointment($secondPatient->id, $appointmentTypeId, '2026-04-26 10:00:00');

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/appointments/admin-list');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $latestAppointmentId)
            ->assertJsonPath('data.0.user.name', 'Patient Two')
            ->assertJsonPath('data.1.user.name', 'Patient One');
    }

    public function test_non_admin_cannot_list_all_appointments(): void
    {
        $patientRoleId = $this->createRole('patient');
        $patient = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($patient);

        $this->getJson('/api/appointments/admin-list')
            ->assertForbidden();
    }

    private function createRole(string $name): int
    {
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
