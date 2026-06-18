<?php

namespace Tests\Unit\Modules\Auth;

use App\Modules\Users\Actions\AdminRegisterUserAction;
use App\Modules\Users\DTOs\AdminRegisterUserDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_creates_user_with_role(): void
    {
        $userRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);

        $action = new AdminRegisterUserAction();
        $user = $action->execute(new AdminRegisterUserDTO(
            name: 'Jane Doe',
            email: 'jane.doe@example.com',
            password: 'secret-123',
            roleId: $userRoleId,
        ));

        $this->assertSame('user', $user->role?->name);
        $this->assertSame('active', $user->account_status);
        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@example.com',
            'account_status' => 'active',
        ]);
    }
}
