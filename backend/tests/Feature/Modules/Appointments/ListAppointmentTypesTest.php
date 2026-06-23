<?php

namespace Tests\Feature\Modules\Appointments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListAppointmentTypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_public_appointment_types_for_booking(): void
    {
        DB::table('appointment_types')->insert([
            [
                'name' => 'ECG',
                'description' => 'Electrocardiogram.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CBC',
                'description' => 'Complete blood count.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/appointments/types');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.name', 'CBC');
        $response->assertJsonPath('data.1.name', 'ECG');
    }
}
