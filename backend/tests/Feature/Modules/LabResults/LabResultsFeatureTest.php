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

    public function test_admin_can_create_lab_result(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);

        Sanctum::actingAs($admin);

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

    public function test_user_cannot_create_lab_result(): void
    {
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);

        Sanctum::actingAs($user);

        $this->postJson('/api/lab-results', [
            'appointment_id' => $appointmentId,
            'result_data' => ['note' => 'x'],
        ])->assertStatus(403);
    }

    public function test_user_can_view_own_released_lab_result(): void
    {
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);
        $labResultId = $this->createLabResult($appointmentId, now()->toDateTimeString());

        Sanctum::actingAs($user);

        $this->getJson("/api/lab-results/{$labResultId}")
            ->assertOk()
            ->assertJsonPath('data.id', $labResultId);
    }

    public function test_user_cannot_view_unreleased_or_other_users_lab_results(): void
    {
        $user = $this->createUserWithRole('user');
        $otherUser = $this->createUserWithRole('user');

        $ownUnreleasedAppointment = $this->createAppointmentForUser($user->id);
        $otherReleasedAppointment = $this->createAppointmentForUser($otherUser->id);

        $ownUnreleasedResultId = $this->createLabResult($ownUnreleasedAppointment, null);
        $otherReleasedResultId = $this->createLabResult($otherReleasedAppointment, now()->toDateTimeString());

        Sanctum::actingAs($user);

        $this->getJson("/api/lab-results/{$ownUnreleasedResultId}")
            ->assertStatus(403);

        $this->getJson("/api/lab-results/{$otherReleasedResultId}")
            ->assertStatus(403);
    }

    public function test_admin_can_release_lab_result(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);
        $labResultId = $this->createLabResult($appointmentId, null);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/lab-results/{$labResultId}/release")
            ->assertOk()
            ->assertJsonPath('data.id', $labResultId);

        $this->assertDatabaseHas('lab_results', [
            'id' => $labResultId,
        ]);
    }

    public function test_user_list_only_shows_own_released_results(): void
    {
        $user = $this->createUserWithRole('user');
        $otherUser = $this->createUserWithRole('user');

        $ownReleasedAppointment = $this->createAppointmentForUser($user->id);
        $ownUnreleasedAppointment = $this->createAppointmentForUser($user->id);
        $otherReleasedAppointment = $this->createAppointmentForUser($otherUser->id);

        $ownReleasedResultId = $this->createLabResult($ownReleasedAppointment, now()->toDateTimeString());
        $this->createLabResult($ownUnreleasedAppointment, null);
        $this->createLabResult($otherReleasedAppointment, now()->toDateTimeString());

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/lab-results');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownReleasedResultId);
    }

    public function test_admin_can_generate_signed_upload_url_for_lab_result_file(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);

        $this->mock(LabResultFileUrlService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('createTemporaryUploadUrl')
                ->once()
                ->andReturn([
                    'url' => 'https://example-s3/upload',
                    'headers' => ['Content-Type' => 'application/pdf'],
                ]);
        });

        Sanctum::actingAs($admin);

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

    public function test_user_cannot_generate_signed_upload_url_for_lab_result_file(): void
    {
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);

        Sanctum::actingAs($user);

        $this->postJson('/api/lab-results/upload-url', [
            'appointment_id' => $appointmentId,
            'file_name' => 'cbc-report.pdf',
            'content_type' => 'application/pdf',
            'size_bytes' => 12000,
        ])->assertStatus(403);
    }

    public function test_user_can_get_signed_download_url_for_own_released_lab_result(): void
    {
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);
        $labResultId = $this->createLabResult($appointmentId, now()->toDateTimeString());

        $this->mock(LabResultFileUrlService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('createTemporaryDownloadUrl')
                ->once()
                ->andReturn('https://example-s3/download');
        });

        Sanctum::actingAs($user);

        $this->getJson("/api/lab-results/{$labResultId}/file-url")
            ->assertOk()
            ->assertJsonPath('data.download_url', 'https://example-s3/download');
    }

    public function test_user_cannot_get_signed_download_url_for_unreleased_lab_result(): void
    {
        $user = $this->createUserWithRole('user');
        $appointmentId = $this->createAppointmentForUser($user->id);
        $labResultId = $this->createLabResult($appointmentId, null);

        Sanctum::actingAs($user);

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

    private function createAppointmentForUser(int $userId): int
    {
        $appointmentTypeId = (int) DB::table('appointment_types')->insertGetId([
            'name' => 'Lab Exam',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('appointments')->insertGetId([
            'user_id' => $userId,
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
