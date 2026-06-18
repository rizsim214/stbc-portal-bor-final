<?php

namespace Tests\Unit\Modules\Auth;

use App\Models\User;
use App\Modules\Users\Actions\DeactivateUserAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeactivateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_toggles_active_user_to_inactive(): void
    {
        $userRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);

        $user = User::factory()->create([
            'role_id' => $userRoleId,
            'account_status' => 'active',
        ]);

        $action = new DeactivateUserAction();
        $result = $action->execute($user->id);

        $this->assertSame('inactive', $result->account_status);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_status' => 'inactive',
        ]);
    }

    public function test_execute_toggles_inactive_user_to_active(): void
    {
        $userRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);

        $user = User::factory()->create([
            'role_id' => $userRoleId,
            'account_status' => 'inactive',
        ]);

        $action = new DeactivateUserAction();
        $result = $action->execute($user->id);

        $this->assertSame('active', $result->account_status);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_status' => 'active',
        ]);
    }
}
