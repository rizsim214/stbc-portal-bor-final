<?php

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_roles(): void
    {
        $adminRoleId = $this->createRole('admin');
        $this->createRole('staff');
        $this->createRole('doctor');
        $this->createRole('radiologist');
        $this->createRole('lab_technologist');
        $this->createRole('patient');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonCount(6, 'data');
    }

    public function test_non_admin_cannot_list_roles(): void
    {
        $patientRoleId = $this->createRole('patient');
        $user = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($user);

        $this->getJson('/api/roles')
            ->assertStatus(403);
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $adminRoleId = $this->createRole('admin');
        $doctorRoleId = $this->createRole('doctor');
        $patientRoleId = $this->createRole('patient');

        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $target = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$target->id}/role", [
            'role_id' => $doctorRoleId,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.role.name', 'doctor')
            ->assertJsonPath('data.staff_status', 'available');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role_id' => $doctorRoleId,
            'staff_status' => 'available',
        ]);
    }

    public function test_admin_can_create_medical_staff_user_with_role_and_status(): void
    {
        $adminRoleId = $this->createRole('admin');
        $doctorRoleId = $this->createRole('doctor');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Dr. House',
            'email' => 'doctor@example.com',
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
            'role_id' => $doctorRoleId,
            'staff_status' => 'on_duty',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.role.name', 'doctor')
            ->assertJsonPath('data.staff_status', 'on_duty');

        $this->assertDatabaseHas('users', [
            'email' => 'doctor@example.com',
            'role_id' => $doctorRoleId,
            'staff_status' => 'on_duty',
        ]);
    }

    public function test_medical_staff_can_update_their_own_status(): void
    {
        $doctorRoleId = $this->createRole('doctor');
        $doctor = User::factory()->create([
            'role_id' => $doctorRoleId,
            'staff_status' => 'available',
        ]);

        Sanctum::actingAs($doctor);

        $response = $this->patchJson('/api/users/me/staff-status', [
            'staff_status' => 'on_break',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.staff_status', 'on_break');

        $this->assertDatabaseHas('users', [
            'id' => $doctor->id,
            'staff_status' => 'on_break',
        ]);
    }

    public function test_patient_cannot_update_staff_status(): void
    {
        $patientRoleId = $this->createRole('patient');
        $patient = User::factory()->create([
            'role_id' => $patientRoleId,
        ]);

        Sanctum::actingAs($patient);

        $this->patchJson('/api/users/me/staff-status', [
            'staff_status' => 'on_duty',
        ])->assertStatus(403);
    }

    private function createRole(string $name): int
    {
        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
        ]);
    }
}
