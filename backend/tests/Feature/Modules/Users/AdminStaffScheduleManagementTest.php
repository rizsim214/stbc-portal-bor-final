<?php

namespace Tests\Feature\Modules\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminStaffScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_update_staff_schedule(): void
    {
        $adminRoleId = $this->createRole('admin');
        $staffRoleId = $this->createRole('staff');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $staff = User::factory()->create([
            'name' => 'Dr. Jane',
            'email' => 'dr.jane@example.com',
            'role_id' => $staffRoleId,
            'sub_role' => 'Cardiologist',
        ]);
        $resourceId = $this->createResourceForUser($staff->id);

        Sanctum::actingAs($admin);

        $this->getJson("/api/users/{$staff->id}/staff-schedule")
            ->assertOk()
            ->assertJsonPath('data.user.name', 'Dr. Jane')
            ->assertJsonPath('data.resource_id', $resourceId);

        $this->patchJson("/api/users/{$staff->id}/staff-schedule", [
            'days' => [
                ['day_of_week' => 0, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 1, 'is_enabled' => true, 'start_time' => '08:30', 'end_time' => '14:30'],
                ['day_of_week' => 2, 'is_enabled' => true, 'start_time' => '08:30', 'end_time' => '14:30'],
                ['day_of_week' => 3, 'is_enabled' => true, 'start_time' => '08:30', 'end_time' => '14:30'],
                ['day_of_week' => 4, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 5, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 6, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
            ],
        ])->assertOk()
            ->assertJsonPath('data.days.1.start_time', '08:30:00');

        $this->assertDatabaseHas('resource_schedules', [
            'resource_id' => $resourceId,
            'day_of_week' => 1,
            'start_time' => '08:30:00',
            'end_time' => '14:30:00',
        ]);
    }

    public function test_non_staff_target_cannot_use_staff_schedule_editor(): void
    {
        $adminRoleId = $this->createRole('admin');
        $patientRoleId = $this->createRole('patient');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $patient = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/users/{$patient->id}/staff-schedule")
            ->assertStatus(422);
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

    private function createResourceForUser(int $userId): int
    {
        return (int) DB::table('resources')->insertGetId([
            'user_id' => $userId,
            'name' => 'Staff Resource',
            'type' => 'Cardiologist',
            'is_active' => true,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
