<?php

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegisterRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ENDPOINT = '/api/auth/register';

    public function test_register_requires_name_email_password_and_confirmation(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }

    public function test_register_requires_unique_email_and_valid_password_confirmation(): void
    {
        $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'First User',
            'email' => 'taken@example.com',
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
        ])->assertCreated();

        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Second User',
            'email' => 'taken@example.com',
            'password' => 'secret-123',
            'password_confirmation' => 'different-123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
            ]);
    }

    public function test_public_register_always_assigns_patient_role(): void
    {
        $adminRoleId = (int) DB::table('roles')->insertGetId([
            'name' => 'admin',
        ]);

        $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
            'role_id' => $adminRoleId,
            'staff_status' => 'on_duty',
        ])->assertCreated();

        $user = User::query()->where('email', 'patient@example.com')->firstOrFail();

        $this->assertSame('patient', $user->role?->name);
        $this->assertNull($user->staff_status);
    }
}
