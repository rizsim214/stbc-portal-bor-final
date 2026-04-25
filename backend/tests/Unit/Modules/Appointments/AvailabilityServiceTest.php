<?php

namespace Tests\Unit\Modules\Appointments;

use App\Models\Appointment;
use App\Models\User;
use App\Modules\Appointments\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_available_returns_true_when_no_overlap_exists(): void
    {
        $service = new AvailabilityService();
        $resourceId = $this->createResource();

        $isAvailable = $service->isAvailable(
            [$resourceId],
            CarbonImmutable::parse('2026-04-25 09:00:00')->toDateTimeString(),
            CarbonImmutable::parse('2026-04-25 10:00:00')->toDateTimeString(),
        );

        $this->assertTrue($isAvailable);
    }

    public function test_is_available_returns_false_when_overlap_exists_for_resource(): void
    {
        $service = new AvailabilityService();
        $user = User::factory()->create();
        $resourceId = $this->createResource();
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
}

