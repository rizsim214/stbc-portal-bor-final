<?php

namespace Tests\Unit\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\ForgotPasswordAction;
use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ForgotPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_sends_reset_link_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $action = new ForgotPasswordAction();
        $dto = new ForgotPasswordDTO(email: 'existing@example.com');

        $message = $action->execute($dto);

        $this->assertIsString($message);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_execute_throws_validation_exception_for_unknown_email(): void
    {
        $action = new ForgotPasswordAction();
        $dto = new ForgotPasswordDTO(email: 'missing@example.com');

        try {
            $action->execute($dto);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }
}

