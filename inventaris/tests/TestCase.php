<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    /**
     * Buat user dengan seluruh permission inventaris (setara Admin Sarpras)
     * supaya test alur utama tidak terhalang middleware permission.
     */
    protected function userWithInventoryAccess(): User
    {
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
            Permission::findOrCreate($permission);
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    /**
     * Buat user yang boleh mengelola profil sendiri (setara Admin/Staf).
     */
    protected function userWithProfileAccess(): User
    {
        Permission::findOrCreate('manage profil');

        $user = User::factory()->create();
        $user->givePermissionTo('manage profil');

        return $user;
    }

    /**
     * Create the application, then make sure the test suite can never touch
     * the real database. Without this, a cached config (php artisan
     * config:cache) makes the :memory: setting in phpunit.xml be ignored and
     * RefreshDatabase would run migrate:fresh on database/database.sqlite.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        if (config('database.default') === 'sqlite') {
            config(['database.connections.sqlite.database' => ':memory:']);
            DB::purge('sqlite');
        }

        return $app;
    }
}
