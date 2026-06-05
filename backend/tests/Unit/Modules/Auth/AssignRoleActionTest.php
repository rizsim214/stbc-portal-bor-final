<?php

namespace Tests\Unit\Modules\Auth;

use App\Models\User;
use App\Modules\Users\Actions\AssignRoleAction;
use App\Modules\Users\DTOs\AssignRoleDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssignRoleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_assigns_role_to_user(): void
    {
        $userRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);
        $adminRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'admin',
        ]);

        $user = User::factory()->create([
            'role_id' => $adminRoleId,
        ]);

        $action = new AssignRoleAction();
        $result = $action->execute(new AssignRoleDTO(
            userId: $user->id,
            roleId: $userRoleId,
        ));

        $this->assertSame('user', $result->role?->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role_id' => $userRoleId,
        ]);
    }

    public function test_execute_reassigns_user_to_admin_role(): void
    {
        $userRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);
        $adminRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'admin',
        ]);

        $user = User::factory()->create([
            'role_id' => $userRoleId,
        ]);

        $action = new AssignRoleAction();
        $result = $action->execute(new AssignRoleDTO(
            userId: $user->id,
            roleId: $adminRoleId,
        ));

        $this->assertSame('admin', $result->role?->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role_id' => $adminRoleId,
        ]);
    }
}
