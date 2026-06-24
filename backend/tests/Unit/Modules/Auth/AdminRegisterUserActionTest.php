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

        $action = $this->app->make(AdminRegisterUserAction::class);
        $user = $action->execute(new AdminRegisterUserDTO(
            name: 'Jane Doe',
            email: 'jane.doe@example.com',
            password: 'secret-123',
            roleId: $userRoleId,
            subRole: null,
        ));

        $this->assertSame('user', $user->role?->name);
        $this->assertSame('active', $user->account_status);
        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@example.com',
            'account_status' => 'active',
        ]);
    }

    public function test_execute_persists_sub_role_for_staff_accounts(): void
    {
        $staffRoleId = (int) DB::table('roles')->where('name', 'staff')->value('id');

        $action = $this->app->make(AdminRegisterUserAction::class);
        $user = $action->execute(new AdminRegisterUserDTO(
            name: 'Dr. Jane Doe',
            email: 'dr.jane@example.com',
            password: 'secret-123',
            roleId: $staffRoleId,
            subRole: 'Cardiologist',
        ));

        $this->assertSame('staff', $user->role?->name);
        $this->assertSame('Cardiologist', $user->sub_role);
        $this->assertDatabaseHas('users', [
            'email' => 'dr.jane@example.com',
            'sub_role' => 'Cardiologist',
        ]);
        $this->assertDatabaseHas('resources', [
            'user_id' => $user->id,
            'type' => 'Cardiologist',
            'is_active' => true,
            'is_available' => true,
        ]);
        $this->assertSame(5, DB::table('resource_schedules')
            ->where('resource_id', DB::table('resources')->where('user_id', $user->id)->value('id'))
            ->count());
    }
}
