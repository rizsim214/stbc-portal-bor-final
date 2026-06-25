<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssignAppointmentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_staff_to_pending_appointment(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId);
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, '08:00:00', '17:00:00');

        Sanctum::actingAs($admin);

        $this->patchJson('/api/appointments/' . $appointmentId . '/assignment', [
            'resource_id' => $resourceId,
        ])->assertOk()
            ->assertJsonPath('data.status', 'assigned')
            ->assertJsonPath('data.resources.0.id', $resourceId)
            ->assertJsonPath('data.allowed_next_statuses.0', 'checkup_ongoing');

        $this->assertDatabaseHas('appointment_resources', [
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
        ]);

        $this->assertDatabaseHas('resource_bookings', [
            'appointment_id' => $appointmentId,
            'resource_id' => $resourceId,
        ]);
    }

    public function test_non_admin_cannot_assign_staff_to_appointment(): void
    {
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId);
        $resourceId = $this->createResource();

        Sanctum::actingAs($patient);

        $this->patchJson('/api/appointments/' . $appointmentId . '/assignment', [
            'resource_id' => $resourceId,
        ])->assertForbidden();
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

    private function createResource(): int
    {
        return (int) DB::table('resources')->insertGetId([
            'name' => 'Radiologist John',
            'type' => 'radiologist',
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
}
