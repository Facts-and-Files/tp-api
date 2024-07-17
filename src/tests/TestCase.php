<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $campaignData = [
        [
            'CampaignId' => 1
        ],
        [
            'CampaignId' => 2
        ]
    ];

    protected static $storyData = [
        [
            'StoryId' => 1
        ],
        [
            'StoryId' => 2
        ],
        [
            'StoryId' => 3
        ]
    ];

    protected static $storyCampaignData = [
        [
            'CampaignId' => 1,
            'StoryId'    => 1
        ],
        [
            'CampaignId' => 1,
            'StoryId'    => 2
        ],
        [
            'CampaignId' => 2,
            'StoryId'    => 3
        ]
    ];

    protected static $itemData = [
        [
            'ItemId'                => 1,
            'StoryId'               => 1,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1
        ],
        [
            'ItemId'                => 2,
            'StoryId'               => 2,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1
        ],
        [
            'ItemId'                => 3,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken('8je2CZ8r1U1JUdclpfSVKyP6gSzF65c16Q6bY6P2EqpGAWSwLvgHjhfuu4FS');

        // run test migration for all already existing databases
        $this->artisan('migrate', ['--path' => 'database/testMigrations']);

        // since we cannot use all migrations from the begining select here specific ones
        // basically since the start of creating tests, all other will be covered
        $additionalMigrations = [
            '2024_03_18_103600_create_user_stats_view.php',
            '2024_03_22_150100_create_campaign_stats_view.php'
        ];

        foreach ($additionalMigrations as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/' . $migration]);
        }
    }
}
