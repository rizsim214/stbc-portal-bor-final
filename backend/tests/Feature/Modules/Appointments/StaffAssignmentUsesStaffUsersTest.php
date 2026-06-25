<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffAssignmentUsesStaffUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_creation_also_creates_linked_assignment_resource(): void
    {
        $adminRoleId = $this->createRole('admin');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $staffRoleId = $this->createRole('staff');

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Dr. Jane Doe',
            'email' => 'dr.jane@example.com',
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
            'role_id' => $staffRoleId,
            'sub_role' => 'Cardiologist',
        ]);

        $staffUserId = (int) $response->json('data.id');

        $this->assertDatabaseHas('resources', [
            'user_id' => $staffUserId,
            'name' => 'Dr. Jane Doe',
            'type' => 'Cardiologist',
            'is_active' => true,
        ]);
    }

    public function test_assignable_resources_include_linked_staff_users(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $staffRoleId = $this->createRole('staff');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $staffUser = User::factory()->create([
            'name' => 'Nurse Joy',
            'role_id' => $staffRoleId,
            'sub_role' => 'Nurse',
            'account_status' => 'active',
        ]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, '2026-04-25 09:00:00');
        $resourceId = $this->createResourceForUser($staffUser->id, 'Legacy Name', 'Legacy Type');
        $this->createResourceSchedule($resourceId, 6, '08:00:00', '17:00:00');

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/appointments/resources?appointment_id='.$appointmentId);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $resourceId)
            ->assertJsonPath('data.0.name', 'Nurse Joy')
            ->assertJsonPath('data.0.type', 'Nurse')
            ->assertJsonPath('data.0.is_available', true);
    }

    public function test_assignable_resources_still_return_staff_users_without_matching_schedule(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $staffRoleId = $this->createRole('staff');
        $appointmentTypeId = $this->createAppointmentType();
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);
        $staffUser = User::factory()->create([
            'name' => 'Dr. Strange',
            'role_id' => $staffRoleId,
            'sub_role' => 'Doctor',
            'account_status' => 'active',
        ]);
        $appointmentId = $this->createAppointment($patient->id, $appointmentTypeId, '2026-04-25 09:00:00');
        $resourceId = $this->createResourceForUser($staffUser->id, 'Old Name', 'Old Type');

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/appointments/resources?appointment_id='.$appointmentId);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $resourceId)
            ->assertJsonPath('data.0.name', 'Dr. Strange')
            ->assertJsonPath('data.0.type', 'Doctor')
            ->assertJsonPath('data.0.is_available', false);
    }

    private function createRole(string $name): int
    {
        $existingId = DB::table('roles')->where('name', $name)->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
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

    private function createResourceForUser(int $userId, string $name, string $type): int
    {
        return (int) DB::table('resources')->insertGetId([
            'user_id' => $userId,
            'name' => $name,
            'type' => $type,
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
