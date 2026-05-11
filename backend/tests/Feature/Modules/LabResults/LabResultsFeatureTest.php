<?php

namespace Tests\Feature\Modules\LabResults;

use App\Models\User;
use App\Modules\LabResults\Services\LabResultFileUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class LabResultsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_lab_result(): void
    {
        $staff = $this->createUserWithRole('staff');
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);

        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/lab-results', [
            'appointment_id' => $appointmentId,
            'file_path' => 'results/cbc-001.pdf',
            'result_data' => ['hemoglobin' => '13.4'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.appointment_id', $appointmentId);

        $this->assertDatabaseHas('lab_results', [
            'appointment_id' => $appointmentId,
            'file_path' => 'results/cbc-001.pdf',
        ]);
    }

    public function test_patient_cannot_create_lab_result(): void
    {
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);

        Sanctum::actingAs($patient);

        $this->postJson('/api/lab-results', [
            'appointment_id' => $appointmentId,
            'result_data' => ['note' => 'x'],
        ])->assertStatus(403);
    }

    public function test_patient_can_view_own_released_lab_result(): void
    {
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);
        $labResultId = $this->createLabResult($appointmentId, now()->toDateTimeString());

        Sanctum::actingAs($patient);

        $this->getJson("/api/lab-results/{$labResultId}")
            ->assertOk()
            ->assertJsonPath('data.id', $labResultId);
    }

    public function test_patient_cannot_view_unreleased_or_other_users_lab_results(): void
    {
        $patient = $this->createUserWithRole('patient');
        $otherPatient = $this->createUserWithRole('patient');

        $ownUnreleasedAppointment = $this->createAppointmentForPatient($patient->id);
        $otherReleasedAppointment = $this->createAppointmentForPatient($otherPatient->id);

        $ownUnreleasedResultId = $this->createLabResult($ownUnreleasedAppointment, null);
        $otherReleasedResultId = $this->createLabResult($otherReleasedAppointment, now()->toDateTimeString());

        Sanctum::actingAs($patient);

        $this->getJson("/api/lab-results/{$ownUnreleasedResultId}")
            ->assertStatus(403);

        $this->getJson("/api/lab-results/{$otherReleasedResultId}")
            ->assertStatus(403);
    }

    public function test_staff_can_release_lab_result(): void
    {
        $staff = $this->createUserWithRole('staff');
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);
        $labResultId = $this->createLabResult($appointmentId, null);

        Sanctum::actingAs($staff);

        $this->patchJson("/api/lab-results/{$labResultId}/release")
            ->assertOk()
            ->assertJsonPath('data.id', $labResultId);

        $this->assertDatabaseHas('lab_results', [
            'id' => $labResultId,
        ]);
    }

    public function test_patient_list_only_shows_own_released_results(): void
    {
        $patient = $this->createUserWithRole('patient');
        $otherPatient = $this->createUserWithRole('patient');

        $ownReleasedAppointment = $this->createAppointmentForPatient($patient->id);
        $ownUnreleasedAppointment = $this->createAppointmentForPatient($patient->id);
        $otherReleasedAppointment = $this->createAppointmentForPatient($otherPatient->id);

        $ownReleasedResultId = $this->createLabResult($ownReleasedAppointment, now()->toDateTimeString());
        $this->createLabResult($ownUnreleasedAppointment, null);
        $this->createLabResult($otherReleasedAppointment, now()->toDateTimeString());

        Sanctum::actingAs($patient);

        $response = $this->getJson('/api/lab-results');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownReleasedResultId);
    }

    public function test_staff_can_generate_signed_upload_url_for_lab_result_file(): void
    {
        $staff = $this->createUserWithRole('staff');
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);

        $this->mock(LabResultFileUrlService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('createTemporaryUploadUrl')
                ->once()
                ->andReturn([
                    'url' => 'https://example-s3/upload',
                    'headers' => ['Content-Type' => 'application/pdf'],
                ]);
        });

        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/lab-results/upload-url', [
            'appointment_id' => $appointmentId,
            'file_name' => 'cbc-report.pdf',
            'content_type' => 'application/pdf',
            'size_bytes' => 12000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.upload_url', 'https://example-s3/upload')
            ->assertJsonPath('data.file_key', fn (string $value): bool => str_starts_with($value, "lab-results/{$appointmentId}/"));
    }

    public function test_patient_cannot_generate_signed_upload_url_for_lab_result_file(): void
    {
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);

        Sanctum::actingAs($patient);

        $this->postJson('/api/lab-results/upload-url', [
            'appointment_id' => $appointmentId,
            'file_name' => 'cbc-report.pdf',
            'content_type' => 'application/pdf',
            'size_bytes' => 12000,
        ])->assertStatus(403);
    }

    public function test_patient_can_get_signed_download_url_for_own_released_lab_result(): void
    {
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);
        $labResultId = $this->createLabResult($appointmentId, now()->toDateTimeString());

        $this->mock(LabResultFileUrlService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('createTemporaryDownloadUrl')
                ->once()
                ->andReturn('https://example-s3/download');
        });

        Sanctum::actingAs($patient);

        $this->getJson("/api/lab-results/{$labResultId}/file-url")
            ->assertOk()
            ->assertJsonPath('data.download_url', 'https://example-s3/download');
    }

    public function test_patient_cannot_get_signed_download_url_for_unreleased_lab_result(): void
    {
        $patient = $this->createUserWithRole('patient');
        $appointmentId = $this->createAppointmentForPatient($patient->id);
        $labResultId = $this->createLabResult($appointmentId, null);

        Sanctum::actingAs($patient);

        $this->getJson("/api/lab-results/{$labResultId}/file-url")
            ->assertStatus(403);
    }

    private function createUserWithRole(string $roleName): User
    {
        $existingRoleId = DB::table('roles')->where('name', $roleName)->value('id');
        $roleId = $existingRoleId
            ? (int) $existingRoleId
            : (int) DB::table('roles')->insertGetId([
                'name' => $roleName,
            ]);

        return User::factory()->create([
            'role_id' => $roleId,
        ]);
    }

    private function createAppointmentForPatient(int $patientId): int
    {
        $appointmentTypeId = (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Lab Exam',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $patientId,
            'appointment_type_id' => $appointmentTypeId,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'scheduled',
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createLabResult(int $appointmentId, ?string $releasedAt): int
    {
        return (int) DB::table('lab_results')->insertGetId([
            'appointment_id' => $appointmentId,
            'file_path' => 'results/default.pdf',
            'result_data' => json_encode(['ok' => true], JSON_THROW_ON_ERROR),
            'released_at' => $releasedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
