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
    }

    protected function makeMigrations(): void
    {
        $files = glob('storage/test-migrations/*.sqlite');

        foreach ($files as $file) {

            $sql = File::get($file);

            DB::unprepared($sql);

        }
    }
}
