<?php

namespace Tests\Unit\Modules\Scheduling;

use App\Models\Appointment;
use App\Models\User;
use App\Modules\Scheduling\DTOs\SchedulingAvailabilityDTO;
use App\Modules\Scheduling\Services\SchedulingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchedulingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_available_returns_true_when_within_schedule_and_no_overlap_exists(): void
    {
        $service = new SchedulingService();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, '08:00:00', '17:00:00');

        $isAvailable = $service->isAvailable(
            [$resourceId],
            CarbonImmutable::parse('2026-04-25 09:00:00')->toDateTimeString(),
            CarbonImmutable::parse('2026-04-25 10:00:00')->toDateTimeString(),
        );

        $this->assertTrue($isAvailable);
    }

    public function test_is_available_returns_false_when_overlap_exists_for_resource(): void
    {
        $service = new SchedulingService();
        $user = User::factory()->create();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, '08:00:00', '17:00:00');
        $appointmentTypeId = $this->createAppointmentType();

        $appointment = Appointment::query()->create([
            'user_id' => $user->id,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => CarbonImmutable::parse('2026-04-25 09:00:00')->toDateTimeString(),
            'end_time' => CarbonImmutable::parse('2026-04-25 10:00:00')->toDateTimeString(),
        ]);
        $appointment->resources()->attach([$resourceId]);

        $isAvailable = $service->isAvailable(
            [$resourceId],
            CarbonImmutable::parse('2026-04-25 09:30:00')->toDateTimeString(),
            CarbonImmutable::parse('2026-04-25 10:30:00')->toDateTimeString(),
        );

        $this->assertFalse($isAvailable);
    }

    public function test_is_available_returns_false_when_outside_resource_schedule(): void
    {
        $service = new SchedulingService();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, '08:00:00', '17:00:00');

        $isAvailable = $service->isAvailable(
            [$resourceId],
            CarbonImmutable::parse('2026-04-25 18:00:00')->toDateTimeString(),
            CarbonImmutable::parse('2026-04-25 19:00:00')->toDateTimeString(),
        );

        $this->assertFalse($isAvailable);
    }

    public function test_get_available_slots_returns_schedule_constrained_slots(): void
    {
        $service = new SchedulingService();
        $resourceId = $this->createResource();
        $this->createResourceSchedule($resourceId, 6, '09:00:00', '11:00:00');

        $result = $service->getAvailableSlots(new SchedulingAvailabilityDTO(
            date: '2026-04-25',
            appointmentTypeId: $this->createAppointmentType(),
            resourceIds: [$resourceId],
        ));

        $this->assertSame('2026-04-25', $result['date']);
        $this->assertSame(4, count($result['slots']));
        $this->assertSame('2026-04-25 09:00:00', $result['slots'][0]['start_time']);
        $this->assertSame('2026-04-25 09:30:00', $result['slots'][0]['end_time']);
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

