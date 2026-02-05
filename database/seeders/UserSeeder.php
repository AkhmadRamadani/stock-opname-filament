<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'Admin Input', 'guard_name' => 'web']);

        // Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
        $admin->assignRole($superAdminRole);

        // Supervisor
        $supervisor = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'spv@example.com',
            'role' => 'supervisor',
        ]);
        $supervisor->assignRole($supervisorRole);

        // Staff Input
        $staff = User::factory()->create([
            'name' => 'Staff Input',
            'email' => 'staff@example.com',
            'role' => 'admin_input',
        ]);
        $staff->assignRole($staffRole);
    }
}
