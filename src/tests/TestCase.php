<?php

namespace Tests;

use Database\Seeders\CompletionStatusDataSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken('8je2CZ8r1U1JUdclpfSVKyP6gSzF65c16Q6bY6P2EqpGAWSwLvgHjhfuu4FS');

        // run test migration for all already existing databases
        $this->artisan('migrate', ['--path' => 'database/testMigrations']);

        // since we cannot use all migrations from the begin we select here specific ones
        // basically since the start of creating tests, all other will be covered
        $additionalMigrations = [
            '2024_03_18_103600_create_user_stats_view.php',
            '2024_03_22_150100_create_campaign_stats_view.php'
        ];

        foreach ($additionalMigrations as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/' . $migration]);
        }

        // populate general immutable table data
        $this->artisan('db:seed', ['--class' => CompletionStatusDataSeeder::class]);
    }
}
