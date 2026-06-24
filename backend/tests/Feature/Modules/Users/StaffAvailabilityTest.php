<?php

namespace Tests\Feature\Modules\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_own_availability(): void
    {
        $staffRoleId = $this->createRole('staff');
        $staff = User::factory()->create([
            'role_id' => $staffRoleId,
            'sub_role' => 'Doctor',
        ]);
        $resourceId = $this->createResourceForUser($staff->id, false);

        Sanctum::actingAs($staff);

        $this->getJson('/api/staff/availability')
            ->assertOk()
            ->assertJsonPath('data.resource_id', $resourceId)
            ->assertJsonPath('data.is_available', false);
    }

    public function test_staff_can_update_own_availability(): void
    {
        $staffRoleId = $this->createRole('staff');
        $staff = User::factory()->create([
            'role_id' => $staffRoleId,
            'sub_role' => 'Doctor',
        ]);
        $resourceId = $this->createResourceForUser($staff->id, true);

        Sanctum::actingAs($staff);

        $this->patchJson('/api/staff/availability', [
            'is_available' => false,
        ])->assertOk()
            ->assertJsonPath('data.resource_id', $resourceId)
            ->assertJsonPath('data.is_available', false);

        $this->assertDatabaseHas('resources', [
            'id' => $resourceId,
            'is_available' => false,
        ]);
    }

    public function test_non_staff_cannot_manage_staff_availability(): void
    {
        $patientRoleId = $this->createRole('patient');
        $patient = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($patient);

        $this->getJson('/api/staff/availability')->assertForbidden();
        $this->patchJson('/api/staff/availability', [
            'is_available' => false,
        ])->assertForbidden();
    }

    public function test_staff_can_view_and_update_own_schedule(): void
    {
        $staffRoleId = $this->createRole('staff');
        $staff = User::factory()->create([
            'role_id' => $staffRoleId,
            'sub_role' => 'Doctor',
        ]);
        $resourceId = $this->createResourceForUser($staff->id, true);
        $this->createResourceSchedule($resourceId, 1, '09:00:00', '16:30:00');

        Sanctum::actingAs($staff);

        $this->getJson('/api/staff/schedule')
            ->assertOk()
            ->assertJsonPath('data.resource_id', $resourceId)
            ->assertJsonPath('data.days.1.day_of_week', 1)
            ->assertJsonPath('data.days.1.is_enabled', true);

        $this->patchJson('/api/staff/schedule', [
            'days' => [
                ['day_of_week' => 0, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 1, 'is_enabled' => true, 'start_time' => '10:00', 'end_time' => '15:00'],
                ['day_of_week' => 2, 'is_enabled' => true, 'start_time' => '09:30', 'end_time' => '16:00'],
                ['day_of_week' => 3, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 4, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 5, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
                ['day_of_week' => 6, 'is_enabled' => false, 'start_time' => null, 'end_time' => null],
            ],
        ])->assertOk()
            ->assertJsonPath('data.days.1.start_time', '10:00:00')
            ->assertJsonPath('data.days.2.start_time', '09:30:00');

        $this->assertDatabaseHas('resource_schedules', [
            'resource_id' => $resourceId,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '15:00:00',
        ]);
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

    private function createResourceForUser(int $userId, bool $isAvailable): int
    {
        return (int) DB::table('resources')->insertGetId([
            'user_id' => $userId,
            'name' => 'Staff Resource',
            'type' => 'Doctor',
            'is_active' => true,
            'is_available' => $isAvailable,
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
