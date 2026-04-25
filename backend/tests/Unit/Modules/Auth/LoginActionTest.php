<?php

namespace Tests\Unit\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\DTOs\LoginDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_returns_token_and_user_for_valid_credentials(): void
    {
        $loginSecret = 'secret-123';

        $user = User::factory()->create([
            'password' => Hash::make($loginSecret),
        ]);

        $action = new LoginAction();
        $dto = new LoginDTO(
            email: $user->email,
            password: $loginSecret,
            deviceName: 'unit-test',
        );

        $result = $action->execute($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertIsString($result['token']);
        $this->assertSame($user->id, $result['user']->id);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_execute_throws_validation_exception_for_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $action = new LoginAction();
        $dto = new LoginDTO(
            email: $user->email,
            password: 'wrong-password',
            deviceName: 'unit-test',
        );

        try {
            $action->execute($dto);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            $this->assertSame(['Invalid credentials.'], $e->errors()['email']);
        }
    }
}
