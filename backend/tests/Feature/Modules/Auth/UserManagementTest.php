<?php

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users_with_roles(): void
    {
        $adminRoleId = $this->createRole('admin');
        $userRoleId = $this->createRole('user');

        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'role_id' => $userRoleId,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $roleNames = collect($response->json('data'))
            ->pluck('role.name')
            ->all();

        $this->assertContains('admin', $roleNames);
        $this->assertContains('user', $roleNames);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $userRoleId = $this->createRole('user');
        $user = User::factory()->create(['role_id' => $userRoleId]);

        Sanctum::actingAs($user);

        $this->getJson('/api/users')
            ->assertStatus(403);
    }

    private function createRole(string $name): int
    {
        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
        ]);
    }
}
