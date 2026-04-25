<?php

namespace Tests\Unit\Modules\Auth;

use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\DTOs\RegisterDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_creates_user_and_returns_token(): void
    {
        $action = new RegisterAction();
        $dto = new RegisterDTO(
            name: 'Test User',
            email: 'register@example.com',
            password: 'secret-123',
            deviceName: 'unit-test',
        );

        $result = $action->execute($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertDatabaseHas('users', [
            'email' => 'register@example.com',
            'name' => 'Test User',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}

