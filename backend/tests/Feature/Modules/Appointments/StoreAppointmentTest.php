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

    public function test_authenticated_user_can_store_appointment(): void
    {
        $user = User::factory()->create();
        $appointmentTypeId = $this->createAppointmentType();
        $resourceId = $this->createResource();
        $start = CarbonImmutable::parse('2026-04-25 09:00:00');
        $end = $start->addHour();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/appointments', [
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
        $user = User::factory()->create();
        $appointmentTypeId = $this->createAppointmentType();
        $resourceId = $this->createResource();
        $start = CarbonImmutable::parse('2026-04-25 09:00:00');
        $end = $start->addHour();

        $existingAppointment = Appointment::query()->create([
            'user_id' => $user->id,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);
        $existingAppointment->resources()->attach([$resourceId]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/appointments', [
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => $start->addMinutes(30)->toDateTimeString(),
            'end_time' => $end->addMinutes(30)->toDateTimeString(),
            'resource_ids' => [$resourceId],
        ]);

        $response->assertStatus(422);
        $this->assertSame('Selected time slot is not available.', $response->json('message'));
        $this->assertDatabaseCount('appointments', 1);
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
