<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->firstOrCreate(['name' => 'admin']);
        Role::query()->firstOrCreate(['name' => 'staff']);
        Role::query()->firstOrCreate(['name' => 'doctor']);
        Role::query()->firstOrCreate(['name' => 'radiologist']);
        Role::query()->firstOrCreate(['name' => 'lab_technologist']);
        Role::query()->firstOrCreate(['name' => 'patient']);
    }
}
