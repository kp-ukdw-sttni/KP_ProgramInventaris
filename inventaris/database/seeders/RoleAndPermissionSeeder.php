<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'manage inventory']);
        Permission::create(['name' => 'view inventory']);

        // Create roles and assign created permissions
        $admin1Role = Role::create(['name' => 'Admin 1 Sarpras']);

        $admin2Role = Role::create(['name' => 'Admin 2 Sarpras']);
        $admin2Role->givePermissionTo(['manage inventory', 'view inventory']);

        // Create users and assign roles
        $admin1 = User::updateOrCreate([
            'email' => 'admin1@sarpras.com',
        ], [
            'name' => 'Admin 1 Sarpras',
            'password' => Hash::make('password'),
        ]);
        $admin1->assignRole($admin1Role);

        $admin2 = User::updateOrCreate([
            'email' => 'admin2@sarpras.com',
        ], [
            'name' => 'Admin 2 Sarpras',
            'password' => Hash::make('password'),
        ]);
        $admin2->assignRole($admin2Role);
    }
}
