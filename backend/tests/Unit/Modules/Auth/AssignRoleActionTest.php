<?php

namespace Tests\Unit\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\AssignRoleAction;
use App\Modules\Auth\DTOs\AssignRoleDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssignRoleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_assigns_role_to_user(): void
    {
        $staffRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'staff',
        ]);
        $patientRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'patient',
        ]);

        $user = User::factory()->create([
            'role_id' => $patientRoleId,
        ]);

        $action = new AssignRoleAction();
        $result = $action->execute(new AssignRoleDTO(
            userId: $user->id,
            roleId: $staffRoleId,
        ));

        $this->assertSame('staff', $result->role?->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role_id' => $staffRoleId,
        ]);
    }
}

