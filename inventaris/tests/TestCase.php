<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
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
