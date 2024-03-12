<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken('8je2CZ8r1U1JUdclpfSVKyP6gSzF65c16Q6bY6P2EqpGAWSwLvgHjhfuu4FS');

        $this->makeMigrations();

        $this->artisan('migrate', ['--path' => 'database/testMigrations']);
    }

    protected function makeMigrations(): void
    {
        $files = glob('storage/test-migrations/*.sqlite');

        DB::unprepared('
            PRAGMA foreign_keys = OFF;
            PRAGMA ignore_check_constraints = OFF;
            PRAGMA auto_vacuum = NONE;
            PRAGMA secure_delete = OFF;
            BEGIN TRANSACTION;
        ');

        foreach ($files as $file) {

            $sql = File::get($file);

            DB::unprepared($sql);

        }

        DB::unprepared('
            COMMIT;
            PRAGMA ignore_check_constraints = ON;
            PRAGMA foreign_keys = ON;
            PRAGMA journal_mode = WAL;
            PRAGMA synchronous = NORMAL;
        ');
    }
}
