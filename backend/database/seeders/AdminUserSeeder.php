<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
        ]);

        $adminEmail = (string) env('INITIAL_ADMIN_EMAIL', 'admin@stbc.local');

        $admin = User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => (string) env('INITIAL_ADMIN_NAME', 'System Admin'),
                'password' => (string) env('INITIAL_ADMIN_PASSWORD', 'password123'),
                'role_id' => $adminRole->id,
            ]
        );

        if ((int) $admin->role_id !== (int) $adminRole->id) {
            $admin->role_id = $adminRole->id;
            $admin->save();
        }
    }
}

