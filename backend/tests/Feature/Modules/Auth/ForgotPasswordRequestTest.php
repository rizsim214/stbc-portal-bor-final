<?php

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    private const FORGOT_PASSWORD_ENDPOINT = '/api/auth/forgot-password';

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson(self::FORGOT_PASSWORD_ENDPOINT, [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_sends_reset_link_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson(self::FORGOT_PASSWORD_ENDPOINT, [
            'email' => 'user@example.com',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPassword::class);
    }
}

