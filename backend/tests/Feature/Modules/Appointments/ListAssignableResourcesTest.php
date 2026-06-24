<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListAssignableResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gets_resources_with_availability_for_the_requested_appointment(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, '2026-04-25 09:00:00');

        $availableResourceId = $this->createResource('Available Staff');
        $wrongScheduleResourceId = $this->createResource('Wrong Schedule Staff');
        $busyResourceId = $this->createResource('Busy Staff');

        $this->createResourceSchedule($availableResourceId, 6, '08:00:00', '17:00:00');
        $this->createResourceSchedule($wrongScheduleResourceId, 6, '13:00:00', '17:00:00');
        $this->createResourceSchedule($busyResourceId, 6, '08:00:00', '17:00:00');

        $busyAppointmentId = $this->createAppointment($patient->id, $appointmentTypeId, '2026-04-25 09:00:00');
        DB::table('appointment_resources')->insert([
            'appointment_id' => $busyAppointmentId,
            'resource_id' => $busyResourceId,
        ]);
        DB::table('resource_bookings')->insert([
            'resource_id' => $busyResourceId,
            'appointment_id' => $busyAppointmentId,
            'start_time' => '2026-04-25 09:00:00',
            'end_time' => '2026-04-25 09:30:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/appointments/resources?appointment_id='.$appointmentId);

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $availableResourceId)
            ->assertJsonPath('data.0.name', 'Available Staff')
            ->assertJsonPath('data.0.is_available', true)
            ->assertJsonPath('data.1.id', $busyResourceId)
            ->assertJsonPath('data.1.is_available', false)
            ->assertJsonPath('data.2.id', $wrongScheduleResourceId)
            ->assertJsonPath('data.2.is_available', false);
    }

    public function test_non_admin_cannot_list_assignable_resources(): void
    {
        $patientRoleId = $this->createRole('patient');
        $patient = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($patient);

        $this->getJson('/api/appointments/resources')
            ->assertForbidden();
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

    private function createResource(string $name): int
    {
        return (int) DB::table('resources')->insertGetId([
            'name' => $name,
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
}
