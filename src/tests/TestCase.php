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

        // and then the rest for all migration are just created or the test
        // (for all already existing databases)
        $this->artisan('migrate', ['--path' => 'database/testMigrations']);

        // since we cannot use all migrations from the begining select here specific ones
        $this->artisan(
            'migrate',
            ['--path' => 'database/migrations/2024_03_18_103600_create_user_stats_view.php']
        );

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
