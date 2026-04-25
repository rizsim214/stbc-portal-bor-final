<?php

namespace Tests\Feature\Modules\Auth;

use Tests\TestCase;

class LoginRequestValidationTest extends TestCase
{
    private const LOGIN_ENDPOINT = '/api/auth/login';
    private const EMAIL_ADDRESS = 'user@example.com';

    public function test_login_requires_email(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'password' => 'secret-123',
            'device_name' => 'web',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'not-an-email',
            'password' => 'secret-123',
            'device_name' => 'web',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => self::EMAIL_ADDRESS,
            'device_name' => 'web',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_rejects_non_string_or_too_long_device_name(): void
    {
        $nonStringResponse = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => self::EMAIL_ADDRESS,
            'password' => 'secret-123',
            'device_name' => 123,
        ]);

        $nonStringResponse->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);

        $tooLongResponse = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => self::EMAIL_ADDRESS,
            'password' => 'secret-123',
            'device_name' => str_repeat('a', 256),
        ]);

        $tooLongResponse->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);
    }
}
