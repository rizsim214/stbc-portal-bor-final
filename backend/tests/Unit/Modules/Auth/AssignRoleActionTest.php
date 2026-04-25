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
            'staff_status' => 'available',
        ]);
    }

    public function test_execute_clears_staff_status_for_non_medical_role(): void
    {
        $staffRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'staff',
        ]);
        $patientRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'patient',
        ]);

        $user = User::factory()->create([
            'role_id' => $staffRoleId,
            'staff_status' => 'on_duty',
        ]);

        $action = new AssignRoleAction();
        $result = $action->execute(new AssignRoleDTO(
            userId: $user->id,
            roleId: $patientRoleId,
        ));

        $this->assertSame('patient', $result->role?->name);
        $this->assertNull($result->staff_status);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role_id' => $patientRoleId,
            'staff_status' => null,
        ]);
    }
}
