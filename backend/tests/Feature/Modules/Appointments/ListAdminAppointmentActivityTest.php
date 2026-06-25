<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListAdminAppointmentActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_recent_appointment_activity(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $existingPatient = User::factory()->create([
            'role_id' => $patientRoleId,
            'name' => 'Existing Patient',
            'created_at' => Carbon::parse('2026-04-20 08:00:00'),
            'updated_at' => Carbon::parse('2026-04-20 08:00:00'),
        ]);
        $newPatient = User::factory()->create([
            'role_id' => $patientRoleId,
            'name' => 'New Patient',
            'created_at' => Carbon::parse('2026-04-26 10:59:30'),
            'updated_at' => Carbon::parse('2026-04-26 10:59:30'),
        ]);

        $olderAppointmentId = $this->createAppointment(
            $existingPatient->id,
            $appointmentTypeId,
            '2026-05-02 09:00:00',
            '2026-04-25 09:00:00',
        );
        $latestAppointmentId = $this->createAppointment(
            $newPatient->id,
            $appointmentTypeId,
            '2026-05-03 11:00:00',
            '2026-04-26 11:00:00',
        );

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/appointments/admin-activity?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $latestAppointmentId)
            ->assertJsonPath('data.0.user.name', 'New Patient')
            ->assertJsonPath('data.0.user.created_at', Carbon::parse('2026-04-26 10:59:30')->toJSON())
            ->assertJsonPath('data.1.id', $olderAppointmentId)
            ->assertJsonPath('data.1.user.name', 'Existing Patient');
    }

    public function test_non_admin_cannot_list_recent_appointment_activity(): void
    {
        $patientRoleId = $this->createRole('patient');
        $patient = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($patient);

        $this->getJson('/api/appointments/admin-activity')
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

    private function createAppointment(
        int $userId,
        int $appointmentTypeId,
        string $startTime,
        string $createdAt,
    ): int {
        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $startTime,
            'end_time' => date('Y-m-d H:i:s', strtotime($startTime . ' +30 minutes')),
            'status' => 'pending',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
