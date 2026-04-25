<?php

namespace Tests\Unit\Modules\Auth;

use App\Modules\Auth\Actions\AdminRegisterUserAction;
use App\Modules\Auth\DTOs\AdminRegisterUserDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_creates_medical_staff_with_default_available_status(): void
    {
        $doctorRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'doctor',
        ]);

        $action = new AdminRegisterUserAction();
        $user = $action->execute(new AdminRegisterUserDTO(
            name: 'Dr. Alice',
            email: 'dr.alice@example.com',
            password: 'secret-123',
            roleId: $doctorRoleId,
            staffStatus: null,
        ));

        $this->assertSame('doctor', $user->role?->name);
        $this->assertSame('available', $user->staff_status);
    }
}

