<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('resources')->insert([
            ['name' => 'Dr. Smith', 'type' => 'doctor'],
            ['name' => 'Radiologist John', 'type' => 'radiologist'],
            ['name' => 'X-Ray Machine 1', 'type' => 'machine'],
        ]);
    }
}
