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

        // Buat permission granular per aksi.
        $permissions = [
            'create barang',
            'edit barang',
            'delete barang',
            'import barang',
            'replace barang',
            'export barang',
            'manage profil',
            'manage ruangan',
            'manage kategori',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin Sarpras: akses penuh (tambah, edit, update, hapus semua fitur).
        $adminRole = Role::firstOrCreate(['name' => 'Admin Sarpras']);
        $adminRole->syncPermissions($permissions);

        // Staf Sarpras: hanya boleh menambah, mengedit, dan mengimpor barang.
        // Fitur lain tetap bisa dilihat, tetapi tidak bisa diubah.
        $stafRole = Role::firstOrCreate(['name' => 'Staf Sarpras']);
        $stafRole->syncPermissions(['create barang', 'edit barang', 'import barang', 'export barang', 'manage profil']);

        // Viewer: hanya bisa melihat semua data, tanpa aksi tulis apa pun.
        $viewerRole = Role::firstOrCreate(['name' => 'Viewer']);
        $viewerRole->syncPermissions([]);

        // Buat/ubah user dan tetapkan role. Email lama (admin1@/admin2@)
        // ikut dipindahkan agar database yang sudah ter-seed tidak
        // menghasilkan akun ganda.
        $admin = User::whereIn('email', ['admin1@sarpras.com', 'admin@sarpras.com'])->first()
            ?? new User();

        $admin->forceFill([
            'email' => 'admin@sarpras.com',
            'name' => 'Admin Sarpras',
            'password' => Hash::make('password'),
        ])->save();
        $admin->syncRoles($adminRole);

        $staf = User::whereIn('email', ['admin2@sarpras.com', 'staf@sarpras.com'])->first()
            ?? new User();

        $staf->forceFill([
            'email' => 'staf@sarpras.com',
            'name' => 'Staf Sarpras',
            'password' => Hash::make('password'),
        ])->save();
        $staf->syncRoles($stafRole);

        $viewer = User::where('email', 'guest@sarpras.com')->first()
            ?? new User();

        $viewer->forceFill([
            'email' => 'guest@sarpras.com',
            'name' => 'Viewer Sarpras',
            'password' => Hash::make('password'),
        ])->save();
        $viewer->syncRoles($viewerRole);

        // Bersihkan role & permission lama setelah user dipindahkan,
        // supaya database lama yang sudah ter-seed tetap rapi.
        Role::whereIn('name', ['Admin 1 Sarpras', 'Admin 2 Sarpras'])->delete();
        Permission::whereIn('name', ['manage inventory', 'view inventory'])->delete();
    }
}
