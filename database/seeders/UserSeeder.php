<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Supervisor
        User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'spv@example.com',
            'role' => 'supervisor',
        ]);

        // Staff Input
        User::factory()->create([
            'name' => 'Staff Input',
            'email' => 'staff@example.com',
            'role' => 'admin_input',
        ]);
    }
}
