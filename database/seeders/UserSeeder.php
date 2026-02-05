<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

        // Create Permissions
        $models = [
            'Barang',
            'KategoriBarang',
            'LaporanStok',
            'TransaksiMasuk',
            'TransaksiKeluar',
            'User',
            'VerifikasiLog',
        ];

        $actions = [
            'view-any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force-delete',
        ];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$model}", 'guard_name' => 'web']);
            }
        }

        // Define Permissions for Supervisor
        // Supervisor: Full access to business data, View access to logs/users
        $supervisorPermissions = [
            // Barang: Full
            'view-any Barang', 'view Barang', 'create Barang', 'update Barang', 'delete Barang', 'restore Barang', 'force-delete Barang',
            // KategoriBarang: Full
            'view-any KategoriBarang', 'view KategoriBarang', 'create KategoriBarang', 'update KategoriBarang', 'delete KategoriBarang', 'restore KategoriBarang', 'force-delete KategoriBarang',
            // TransaksiMasuk: Full
            'view-any TransaksiMasuk', 'view TransaksiMasuk', 'create TransaksiMasuk', 'update TransaksiMasuk', 'delete TransaksiMasuk', 'restore TransaksiMasuk', 'force-delete TransaksiMasuk',
            // TransaksiKeluar: Full
            'view-any TransaksiKeluar', 'view TransaksiKeluar', 'create TransaksiKeluar', 'update TransaksiKeluar', 'delete TransaksiKeluar', 'restore TransaksiKeluar', 'force-delete TransaksiKeluar',
            // LaporanStok: Full
            'view-any LaporanStok', 'view LaporanStok', 'create LaporanStok', 'update LaporanStok', 'delete LaporanStok', 'restore LaporanStok', 'force-delete LaporanStok',
            // VerifikasiLog: View
            'view-any VerifikasiLog', 'view VerifikasiLog',
            // User: View
            'view-any User', 'view User',
        ];
        $supervisorRole->syncPermissions($supervisorPermissions);

        // Define Permissions for Admin Input (Staff)
        // Staff: Create/View business data, View logs, No user access
        $staffPermissions = [
            // Barang: View, Create, Update
            'view-any Barang', 'view Barang', 'create Barang', 'update Barang',
            // KategoriBarang: View, Create
            'view-any KategoriBarang', 'view KategoriBarang', 'create KategoriBarang',
            // TransaksiMasuk: View, Create
            'view-any TransaksiMasuk', 'view TransaksiMasuk', 'create TransaksiMasuk',
            // TransaksiKeluar: View, Create
            'view-any TransaksiKeluar', 'view TransaksiKeluar', 'create TransaksiKeluar',
             // LaporanStok: View, Create (Draft)
            'view-any LaporanStok', 'view LaporanStok', 'create LaporanStok',
             // VerifikasiLog: View
            'view-any VerifikasiLog', 'view VerifikasiLog',
        ];
        $staffRole->syncPermissions($staffPermissions);

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($superAdminRole);

        // Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'spv@example.com'],
            [
                'name' => 'Supervisor User',
                'password' => 'password',
                'role' => 'supervisor',
                'email_verified_at' => now(),
            ]
        );
        $supervisor->assignRole($supervisorRole);

        // Staff Input
        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Input',
                'password' => 'password',
                'role' => 'admin_input',
                'email_verified_at' => now(),
            ]
        );
        $staff->assignRole($staffRole);
    }
}
