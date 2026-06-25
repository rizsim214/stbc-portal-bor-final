<?php

namespace Tests\Feature\Modules\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateOwnPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_password(): void
    {
        $user = $this->createUser('temp-secret-123');

        Sanctum::actingAs($user);

        $this->patchJson('/api/users/me/password', [
            'old_password' => 'temp-secret-123',
            'new_password' => 'fresh-secret-456',
            'new_password_confirmation' => 'fresh-secret-456',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Password updated successfully.');

        $user->refresh();

        $this->assertTrue(Hash::check('fresh-secret-456', $user->password));
        $this->assertFalse(Hash::check('temp-secret-123', $user->password));
    }

    public function test_password_update_requires_correct_old_password(): void
    {
        $user = $this->createUser('temp-secret-123');

        Sanctum::actingAs($user);

        $this->patchJson('/api/users/me/password', [
            'old_password' => 'wrong-secret',
            'new_password' => 'fresh-secret-456',
            'new_password_confirmation' => 'fresh-secret-456',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['old_password']);
    }

    public function test_password_update_requires_confirmation(): void
    {
        $user = $this->createUser('temp-secret-123');

        Sanctum::actingAs($user);

        $this->patchJson('/api/users/me/password', [
            'old_password' => 'temp-secret-123',
            'new_password' => 'fresh-secret-456',
            'new_password_confirmation' => 'different-secret-456',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    private function createUser(string $password): User
    {
        $roleId = (int) (DB::table('roles')->where('name', 'patient')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'patient']));

        return User::factory()->create([
            'role_id' => $roleId,
            'password' => $password,
        ]);
    }
}
