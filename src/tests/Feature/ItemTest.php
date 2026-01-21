<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Tests\TestCase;

class ItemTest extends TestCase
{
    private static $endpoint = 'items';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
    }

    public function test_get_all_items(): void
    {
        $this->markTestSkipped('must be revisited.');
        // $awaitedSuccess = ['success' => true];
        // $awaitedData = ['data' => ItemDataSeeder::$data];
        //
        // $response = $this->get(self::$endpoint);
        //
        // print_r($response['data']);
        //
        // $response
        //     ->assertOk()
        //     ->assertJson($awaitedSuccess)
        //     ->assertJson($awaitedData);
    }

    public function test_get_a_non_existent_item_returns_404(): void
    {
        $queryParams = '/999999999999';
        $awaitedSuccess = ['success' => false];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_get_basic_data_of_a_single_item(): void
    {
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ItemDataSeeder::$data[0];
        unset($awaitedData['CompletionStatusId']);

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJsonFragment($awaitedData);
    }

    public function test_get_properties_within_a_single_item(): void
    {
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                'Properties' => array_values(array_filter(
                    PropertyDataSeeder::$data,
                    fn($p) => in_array($p['PropertyId'], array_column(
                        array_filter(
                            ItemPropertyDataSeeder::$data,
                            fn($ip) => $ip['ItemId'] === ItemDataSeeder::$data[0]['ItemId']
                        ),
                        'PropertyId'
                    ))
                ))
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_transcription_within_a_single_item(): void
    {
        $itemId = ItemDataSeeder::$data[0]['ItemId'];
        $queryParams = '/' . $itemId;
        $awaitedSuccess = ['success' => true];
        $transcription = array_filter(TranscriptionDataSeeder::$data, fn($t) => $t['ItemId'] === $itemId && $t['CurrentVersion'] === true)[0];
        $awaitedData = [
            'data' => [
                'Transcription' => [
                    'UserId' => $transcription['UserId'],
                    'TranscriptionText' => $transcription['TextNoTags'],
                    'Text' => $transcription['Text'],
                    'CurrentVersion' => $transcription['CurrentVersion'],
                    'NoText' => $transcription['NoText'],
                ],
            ],
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_add_an_already_attached_property_to_an_item_return_422(): void
    {
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'] . '/properties';
        $propertyId = PropertyDataSeeder::$data[2]['PropertyId'];
        $updateData = ['PropertyId' => 2];
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_add_a_non_existent_property_to_an_item_return_404(): void
    {
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'] . '/properties';
        $propertyId = PropertyDataSeeder::$data[2]['PropertyId'];
        $updateData = ['PropertyId' => 1000];
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_add_a_property_to_an_item(): void
    {
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'] . '/properties';
        $propertyId = PropertyDataSeeder::$data[2]['PropertyId'];
        $updateData = ['PropertyId' => $propertyId];
        foreach(PropertyDataSeeder::$data as $property) {
            if ($property['PropertyId'] === $propertyId) {
                $awaitedProperty = $property;
            }
        }
        $awaitedSuccess = ['success' => true];

        $response = $this->post(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJsonFragment($awaitedProperty);
    }

    public function test_remove_a_non_existent_property_from_an_item_return_404(): void
    {
        $propertyId = 1000;
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'] . '/properties/' . $propertyId;
        $awaitedSuccess = ['success' => false];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_remove_an_already_detached_property_from_an_item_return_422(): void
    {
        $propertyId = PropertyDataSeeder::$data[2]['PropertyId'];
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'] . '/properties/' . $propertyId;
        $awaitedSuccess = ['success' => false];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_remove_a_property_from_an_item(): void
    {
        $propertyId = PropertyDataSeeder::$data[1]['PropertyId'];
        $queryParams = '/' . ItemDataSeeder::$data[0]['ItemId'] . '/properties/' . $propertyId;
        $awaitedSuccess = ['success' => true];
        foreach(PropertyDataSeeder::$data as $property) {
            if ($property['PropertyId'] === $propertyId) {
                $notAwaitedProperty = $property;
            }
        }

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJsonMissing($notAwaitedProperty);
    }
}
