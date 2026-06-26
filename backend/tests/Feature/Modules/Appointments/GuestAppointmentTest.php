<?php

namespace Tests\Feature\Modules\Appointments;

use App\Mail\GuestAppointmentAccountCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private const APPOINTMENT_FULL_NAME = 'Juan Dela Cruz';
    private const APPOINTMENT_EMAIL = 'juan@example.com';
    private const APPOINTMENT_START_TIME = '2026-04-25 09:00:00';
    private const APPOINTMENT_END_TIME = '2026-04-25 09:30:00';
    private const APPOINTMENT_NOTES = 'Patient requests CBC and urinalysis for pre-employment requirements.';

    public function test_guest_can_submit_pending_appointment_request_and_create_patient_account(): void
    {
        Mail::fake();

        $patientRoleId = $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();

        $response = $this->postJson('/api/appointments/guest', [
            'name' => self::APPOINTMENT_FULL_NAME,
            'email' => self::APPOINTMENT_EMAIL,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
            'notes' => self::APPOINTMENT_NOTES,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Appointment request submitted and patient account created successfully.')
            ->assertJsonPath('data.user.name', self::APPOINTMENT_FULL_NAME)
            ->assertJsonPath('data.user.email', self::APPOINTMENT_EMAIL)
            ->assertJsonPath('data.user.role.name', 'patient')
            ->assertJsonPath('data.appointment.appointment_type_id', $appointmentTypeId)
            ->assertJsonPath('data.appointment.status', 'pending')
            ->assertJsonPath('data.appointment.notes', self::APPOINTMENT_NOTES);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertNull($response->json('data.temporary_password'));

        $userId = (int) DB::table('users')->where('email', self::APPOINTMENT_EMAIL)->value('id');

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'name' => self::APPOINTMENT_FULL_NAME,
            'email' => self::APPOINTMENT_EMAIL,
            'role_id' => $patientRoleId,
            'account_status' => 'active',
        ]);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $userId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
            'status' => 'pending',
            'notes' => self::APPOINTMENT_NOTES,
        ]);

        $appointmentId = (int) DB::table('appointments')
            ->where('user_id', $userId)
            ->value('id');

        $this->assertDatabaseMissing('appointment_resources', [
            'appointment_id' => $appointmentId,
        ]);

        $this->assertDatabaseMissing('resource_bookings', [
            'appointment_id' => $appointmentId,
        ]);

        Mail::assertSent(GuestAppointmentAccountCreated::class, function (GuestAppointmentAccountCreated $mail) use ($appointmentId): bool {
            return $mail->hasTo(self::APPOINTMENT_EMAIL)
                && $mail->patientName === self::APPOINTMENT_FULL_NAME
                && $mail->temporaryPassword !== ''
                && $mail->appointment->id === $appointmentId;
        });
    }

    public function test_guest_cannot_submit_overlapping_appointment_request(): void
    {
        $this->createRole('patient');
        $appointmentTypeId = $this->createAppointmentType();

        DB::table('users')->insert([
            'name' => 'Existing Patient',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role_id' => DB::table('roles')->where('name', 'patient')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('appointments')->insert([
            'user_id' => (int) DB::table('users')->where('email', 'existing@example.com')->value('id'),
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/appointments/guest', [
            'name' => self::APPOINTMENT_FULL_NAME,
            'email' => self::APPOINTMENT_EMAIL,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => self::APPOINTMENT_START_TIME,
            'end_time' => self::APPOINTMENT_END_TIME,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Selected time slot is not available.');
    }

    private function createRole(string $name): int
    {
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
}
