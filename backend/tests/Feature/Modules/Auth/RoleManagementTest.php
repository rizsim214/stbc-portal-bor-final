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
        $this->createRole('patient');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
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
        $staffRoleId = $this->createRole('staff');
        $patientRoleId = $this->createRole('patient');

        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $target = User::factory()->create(['role_id' => $patientRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$target->id}/role", [
            'role_id' => $staffRoleId,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.role.name', 'staff');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role_id' => $staffRoleId,
        ]);
    }

    private function createRole(string $name): int
    {
        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
        ]);
    }
}

