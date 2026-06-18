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
        $this->createRole('user');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_non_admin_cannot_list_roles(): void
    {
        $userRoleId = $this->createRole('user');
        $user = User::factory()->create(['role_id' => $userRoleId]);

        Sanctum::actingAs($user);

        $this->getJson('/api/roles')
            ->assertStatus(403);
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $adminRoleId = $this->createRole('admin');
        $userRoleId = $this->createRole('user');

        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $target = User::factory()->create(['role_id' => $userRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$target->id}/role", [
            'role_id' => $adminRoleId,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.role.name', 'admin');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role_id' => $adminRoleId,
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $adminRoleId = $this->createRole('admin');
        $userRoleId = $this->createRole('user');

        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $target = User::factory()->create([
            'role_id' => $userRoleId,
            'account_status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$target->id}/toggle-status");

        $response->assertOk()
            ->assertJsonPath('message', 'User status updated successfully.')
            ->assertJsonPath('data.account_status', 'inactive');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'account_status' => 'inactive',
        ]);

        $response = $this->patchJson("/api/users/{$target->id}/toggle-status");

        $response->assertOk()
            ->assertJsonPath('message', 'User status updated successfully.')
            ->assertJsonPath('data.account_status', 'active');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'account_status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_toggle_user_status(): void
    {
        $userRoleId = $this->createRole('user');

        $user = User::factory()->create(['role_id' => $userRoleId]);
        $target = User::factory()->create(['role_id' => $userRoleId]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/users/{$target->id}/toggle-status")
            ->assertStatus(403);
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $adminRoleId = $this->createRole('admin');
        $userRoleId = $this->createRole('user');
        $admin = User::factory()->create(['role_id' => $adminRoleId]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Dr. House',
            'email' => 'user@example.com',
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
            'role_id' => $userRoleId,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.role.name', 'user');

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role_id' => $userRoleId,
        ]);
    }

    private function createRole(string $name): int
    {
        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
        ]);
    }
}
