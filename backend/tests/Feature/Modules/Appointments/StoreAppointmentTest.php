<?php

namespace Tests\Feature\Modules\Appointments;

use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private const START_TIME = '09:00:00';
    private const END_TIME = '16:30:00';
    private const APPOINTMENT_API_URL = '/api/appointments';

    public function test_authenticated_user_can_store_appointment(): void
    {
        $user = $this->createUser();
        $appointmentTypeId = $this->createAppointmentType();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, self::START_TIME, self::END_TIME);
        $start = CarbonImmutable::parse('2026-04-25 09:00:00');
        $end = $start->addHour();

        Sanctum::actingAs($user);

        $response = $this->postJson(self::APPOINTMENT_API_URL, [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'resource_ids' => [$resourceId],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);

        $appointment = Appointment::query()->firstOrFail();
        $this->assertDatabaseHas('appointment_resources', [
            'appointment_id' => $appointment->id,
            'resource_id' => $resourceId,
        ]);
    }

    public function test_store_appointment_returns_422_for_overlapping_resource_booking(): void
    {
        $user = $this->createUser();
        $appointmentTypeId = $this->createAppointmentType();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, self::START_TIME, self::END_TIME);
        $start = CarbonImmutable::parse('2026-04-25 09:00:00');
        $end = $start->addHour();

        Sanctum::actingAs($user);

        $this->postJson(self::APPOINTMENT_API_URL, [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'resource_ids' => [$resourceId],
        ])->assertCreated();

        $response = $this->postJson(self::APPOINTMENT_API_URL, [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $start->addMinutes(30)->toDateTimeString(),
            'end_time' => $end->addMinutes(30)->toDateTimeString(),
            'resource_ids' => [$resourceId],
        ]);

        $response->assertStatus(422);
        $this->assertSame('Selected time slot is not available.', $response->json('message'));
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_store_appointment_returns_422_when_request_is_outside_resource_schedule(): void
    {
        $user = $this->createUser();
        $appointmentTypeId = $this->createAppointmentType();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, self::START_TIME, self::END_TIME);

        Sanctum::actingAs($user);

        $response = $this->postJson(self::APPOINTMENT_API_URL, [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => '2026-04-25 18:00:00',
            'end_time' => '2026-04-25 19:00:00',
            'resource_ids' => [$resourceId],
        ]);

        $response->assertStatus(422);
        $this->assertSame('Selected time slot is not available.', $response->json('message'));
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

    private function createUser(): User
    {
        $roleId = (int) DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);

        return User::factory()->create([
            'role_id' => $roleId,
        ]);
    }
}
