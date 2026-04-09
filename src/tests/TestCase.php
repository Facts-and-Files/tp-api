<?php

namespace Tests;

use Database\Seeders\CompletionStatusDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Database\Seeders\HtrDataDataSeeder;
use Database\Seeders\HtrDataLanguageDataSeeder;
use Database\Seeders\HtrDataRevisionDataSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

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
            '2024_03_22_150100_create_campaign_stats_view.php',
            '2024_07_31_094900_add_manifest_to_story_table.php',
            '2024_09_19_115300_create_transcription_provider_table.php',
            '2025_07_21_125300_create_place_details_view.php',
            '2026_02_16_130200_create_export_cache_table.php',
            '2026_04_08_080753_create_job_batches_table.php',
        ];

        foreach ($additionalMigrations as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/' . $migration]);
        }

        // populate general immutable table data
        $this->artisan('db:seed', ['--class' => CompletionStatusDataSeeder::class]);

        // TranscriptionProvider is already populated by database migration
        // $this->artisan('db:seed', ['--class' => TranscriptionProviderDataSeeder::class]);
    }

    protected function seedAllItemRelatedTables(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->artisan('db:seed', ['--class' => StoryDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => LanguageDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => ItemDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => PropertyDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => HtrDataDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => HtrDataLanguageDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => HtrDataRevisionDataSeeder::class]);

        Schema::enableForeignKeyConstraints();
    }
}
