<?php

namespace Tests\Feature\Modules\Scheduling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SchedulingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_schedule_based_slots(): void
    {
        $user = User::factory()->create();
        $resourceId = $this->createResource();
        $appointmentTypeId = $this->createAppointmentType();
        $this->createResourceSchedule($resourceId, 6, '09:00:00', '10:00:00');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/scheduling/availability?date=2026-04-25&appointment_type_id='.$appointmentTypeId.'&resource_ids[]='.$resourceId);

        $response->assertOk()
            ->assertJsonPath('date', '2026-04-25')
            ->assertJsonPath('slot_minutes', 30)
            ->assertJsonCount(2, 'slots');
    }

    public function test_guest_cannot_fetch_scheduling_slots(): void
    {
        $this->getJson('/api/scheduling/availability?date=2026-04-25&appointment_type_id=1&resource_ids[]=1')
            ->assertStatus(401);
    }

    private function createAppointmentType(): int
    {
        return (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Checkup',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createResource(): int
    {
        return (int) DB::table('resources')->insertGetId([
            'name' => 'Dr. Test',
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

