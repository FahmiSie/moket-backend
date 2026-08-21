<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@moket.id',
            'password' => bcrypt('moket@admin123'),
            'role' => 'super_admin',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Talent User',
            'email' => 'talent@moket.id',
            'password' => bcrypt('moket@talent123'),
            'role' => 'talent',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Mentor User',
            'email' => 'mentor@moket.id',
            'password' => bcrypt('moket@mentor123'),
            'role' => 'mentor',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Reguler User',
            'email' => 'user@moket.id',
            'password' => bcrypt('moket@user123'),
            'role' => 'user',
        ]);
    }
}
