<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateAppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_progress_appointment_status_through_valid_flow(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, 'assigned');
        $resourceId = $this->createResource();
        $this->assignResource($appointmentId, $resourceId);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/appointments/{$appointmentId}/status", [
            'status' => 'checkup_ongoing',
        ])->assertOk()
            ->assertJsonPath('data.status', 'checkup_ongoing')
            ->assertJsonPath('data.allowed_next_statuses.0', 'awaiting_result');

        $this->patchJson("/api/appointments/{$appointmentId}/status", [
            'status' => 'awaiting_result',
        ])->assertOk()
            ->assertJsonPath('data.status', 'awaiting_result')
            ->assertJsonPath('data.allowed_next_statuses.0', 'releasing_lab_result');
    }

    public function test_status_transition_is_rejected_when_not_in_allowed_sequence(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, 'assigned');
        $resourceId = $this->createResource();
        $this->assignResource($appointmentId, $resourceId);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/appointments/{$appointmentId}/status", [
            'status' => 'completed',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'APPOINTMENT_STATUS_TRANSITION_INVALID');
    }

    public function test_progression_requires_assigned_staff(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, 'assigned');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/appointments/{$appointmentId}/status", [
            'status' => 'checkup_ongoing',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'APPOINTMENT_REQUIRES_ASSIGNED_STAFF');
    }

    public function test_show_appointment_returns_allowed_next_statuses(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, 'awaiting_result');

        Sanctum::actingAs($admin);

        $this->getJson("/api/appointments/{$appointmentId}")
            ->assertOk()
            ->assertJsonPath('data.allowed_next_statuses.0', 'releasing_lab_result');
    }

    public function test_completing_releasing_lab_result_requires_uploaded_lab_result(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, 'releasing_lab_result');
        $resourceId = $this->createResource();
        $this->assignResource($appointmentId, $resourceId);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/appointments/{$appointmentId}/status", [
            'status' => 'completed',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'APPOINTMENT_REQUIRES_LAB_RESULT');
    }

    public function test_completing_appointment_releases_uploaded_lab_result(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, 'releasing_lab_result');
        $resourceId = $this->createResource();
        $this->assignResource($appointmentId, $resourceId);
        $labResultId = $this->createLabResult($appointmentId, null);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/appointments/{$appointmentId}/status", [
            'status' => 'completed',
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.lab_result.id', $labResultId);

        $this->assertDatabaseMissing('lab_results', [
            'id' => $labResultId,
            'released_at' => null,
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

    private function createAppointmentType(): int
    {
        return (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Medical Check-up',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(int $userId, int $appointmentTypeId, string $status): int
    {
        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => '2026-06-24 09:00:00',
            'end_time' => '2026-06-24 09:30:00',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createResource(): int
    {
        return (int) DB::table('resources')->insertGetId([
            'name' => 'Dr. Workflow',
            'type' => 'doctor',
            'is_active' => true,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assignResource(int $appointmentId, int $resourceId): void
    {
        DB::table('appointment_resources')->insert([
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
        ]);

        DB::table('resource_bookings')->insert([
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
            'start_time' => '2026-06-24 09:00:00',
            'end_time' => '2026-06-24 09:30:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
