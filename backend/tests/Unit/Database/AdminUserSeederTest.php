<?php

namespace Tests\Unit\Database;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_seeder_creates_initial_admin_account(): void
    {
        config()->set('app.env', 'testing');
        putenv('INITIAL_ADMIN_NAME=Master Admin');
        putenv('INITIAL_ADMIN_EMAIL=master@example.com');
        putenv('INITIAL_ADMIN_PASSWORD=Secret123!');
        $_ENV['INITIAL_ADMIN_NAME'] = 'Master Admin';
        $_ENV['INITIAL_ADMIN_EMAIL'] = 'master@example.com';
        $_ENV['INITIAL_ADMIN_PASSWORD'] = 'Secret123!';
        $_SERVER['INITIAL_ADMIN_NAME'] = 'Master Admin';
        $_SERVER['INITIAL_ADMIN_EMAIL'] = 'master@example.com';
        $_SERVER['INITIAL_ADMIN_PASSWORD'] = 'Secret123!';

        $this->seed(RoleSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'master@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertSame('Master Admin', $admin->name);
        $this->assertSame('admin', $admin->role?->name);
    }
}

