<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'manage inventory']);
        Permission::create(['name' => 'view inventory']);

        // Create roles and assign created permissions
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        
        $adminSarprasRole = Role::create(['name' => 'Admin Sarpras']);
        $adminSarprasRole->givePermissionTo(['manage inventory', 'view inventory']);

        // Create users and assign roles
        $superAdmin = User::updateOrCreate([
            'email' => 'superadmin@sarpras.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole($superAdminRole);

        $adminSarpras = User::updateOrCreate([
            'email' => 'admin@sarpras.com',
        ], [
            'name' => 'Admin Sarpras',
            'password' => Hash::make('password'),
        ]);
        $adminSarpras->assignRole($adminSarprasRole);
    }
}
