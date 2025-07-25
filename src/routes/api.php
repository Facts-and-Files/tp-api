<?php

use App\Http\Controllers\AutoEnrichmentController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignStatsController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HtrDataController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemPropertyController;
use App\Http\Controllers\ItemStatsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SolrController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryStatsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamStatsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserItemsController;
use App\Http\Controllers\UserStatsController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TranscriptionProviderController;
use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/documentation/{filename}', function ($filename) {
    $filePath = storage_path('api-docs/' . $filename);

    if (file_exists($filePath)) {
        return response(File::get($filePath))
            ->header('Content-Type', 'application/x-yaml');
    } else {
        abort(404, 'File not found');
    }
});

Route::get('/health', [HealthController::class, 'check']);

Route::middleware(['auth:api'])->group(function() {
    Route::get('/htrdata', [HtrDataController::class, 'index']);
    Route::post('/htrdata', [HtrDataController::class, 'store']);
    Route::get('/htrdata/{id}', [HtrDataController::class, 'show']);
    Route::put('/htrdata/{id}', [HtrDataController::class, 'update']);
    Route::delete('/htrdata/{id}', [HtrDataController::class, 'destroy']);

    Route::get('/items/statistics', [ItemStatsController::class, 'index']);
    Route::post('/items/statistics', [ItemStatsController::class, 'store']);
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::get('/items/{id}/htrdata', [HtrDataController::class, 'showByItemId']);
    Route::get('/items/{id}/htrdata/active', [HtrDataController::class, 'showActiveByItemId']);
    Route::get('/items/{id}/autoenrichments', [AutoEnrichmentController::class, 'showByItemId']);
    Route::get('/items/{id}/statistics', [ItemStatsController::class, 'show']);
    Route::get('/items/{id}/persons', [PersonController::class, 'showByItemId']);
    Route::get('/items/{id}/places', [PlaceController::class, 'showByItemId']);
    Route::post('/items/{id}/properties', [ItemPropertyController::class, 'attach']);
    Route::delete('/items/{id}/properties/{propertyId}', [ItemPropertyController::class, 'detach']);

    Route::get('/stories/campaigns', [StoryController::class, 'showCampaignsByStories']);
    Route::get('/stories', [StoryController::class, 'index']);
    Route::get('/stories/{id}', [StoryController::class, 'show']);
    Route::get('/stories/{id}/autoenrichments', [AutoEnrichmentController::class, 'showByStoryId']);
    Route::get('/stories/{id}/places', [PlaceController::class, 'showByStoryId']);
    Route::put('/stories/{id}', [StoryController::class, 'update']);
    Route::delete('/stories/{id}', [StoryController::class, 'destroy']);
    Route::get('/stories/{id}/campaigns', [StoryController::class, 'showCampaigns']);
    Route::put('/stories/{id}/campaigns', [StoryController::class, 'updateCampaigns']);
    Route::put('/stories/{id}/add-campaigns', [StoryController::class, 'addCampaigns']);
    Route::get('/stories/{id}/statistics', [StoryStatsController::class, 'show']);

    Route::get('/autoenrichments', [AutoEnrichmentController::class, 'index']);
    Route::post('/autoenrichments', [AutoEnrichmentController::class, 'store']);
    Route::post('/autoenrichments/bulkimports', [AutoEnrichmentController::class, 'storeBulk']);
    Route::get('/autoenrichments/{id}', [AutoEnrichmentController::class, 'show']);
    Route::put('/autoenrichments/{id}', [AutoEnrichmentController::class, 'update']);
    Route::delete('/autoenrichments/{id}', [AutoEnrichmentController::class, 'destroy']);

    Route::get('/languages', [LanguageController::class, 'index']);
    Route::get('/languages/{id}', [LanguageController::class, 'show']);

    Route::get('/users/wpuserids', [UserController::class, 'showWPUserIdsByUserId']);
    Route::get('/users/statistics', [UserStatsController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/users/{id}/statistics', [UserStatsController::class, 'show']);
    Route::get('/users/{id}/htrdata', [HtrDataController::class, 'showByUserId']);
    Route::get('/users/{id}/items', [UserItemsController::class, 'index']);

    Route::get('/scores', [ScoreController::class, 'index']);
    Route::post('/scores', [ScoreController::class, 'store']);

    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{id}', [TeamController::class, 'show']);
    Route::put('/teams/{id}', [TeamController::class, 'update']);
    Route::delete('/teams/{id}', [TeamController::class, 'destroy']);
    Route::get('/teams/{id}/statistics', [TeamStatsController::class, 'show']);

    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
    Route::get('/campaigns/{id}/statistics', [CampaignStatsController::class, 'show']);

    Route::get('/datasets', [DatasetController::class, 'index']);
    Route::post('/datasets', [DatasetController::class, 'store']);
    Route::get('/datasets/{id}', [DatasetController::class, 'show']);
    Route::put('/datasets/{id}', [DatasetController::class, 'update']);
    Route::delete('/datasets/{id}', [DatasetController::class, 'destroy']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{id}/places', [PlaceController::class, 'showByProjectId']);

    Route::get('/statistics', [StatisticsController::class, 'index']);
    Route::get('/statistics/alltime', [StatisticsController::class, 'alltimeIndex']);

    Route::get('/persons', [PersonController::class, 'index']);
    Route::post('/persons', [PersonController::class, 'store']);
    Route::get('/persons/{id}', [PersonController::class, 'show']);
    Route::put('/persons/{id}', [PersonController::class, 'update']);
    Route::delete('/persons/{id}', [PersonController::class, 'destroy']);

    Route::get('/places', [PlaceController::class, 'index']);
    Route::post('/places', [PlaceController::class, 'store']);
    Route::get('/places/{id}', [PlaceController::class, 'show']);
    Route::put('/places/{id}', [PlaceController::class, 'update']);
    Route::delete('/places/{id}', [PlaceController::class, 'destroy']);

    Route::get('/properties', [PropertyController::class, 'index']);
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);
    Route::put('/properties/{id}', [PropertyController::class, 'update']);
    Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);

    Route::get('/transcription-providers', [TranscriptionProviderController::class, 'index']);
    Route::post('/transcription-providers', [TranscriptionProviderController::class, 'store']);
    Route::get('/transcription-providers/{id}', [TranscriptionProviderController::class, 'show']);
    Route::put('/transcription-providers/{id}', [TranscriptionProviderController::class, 'update']);
    Route::delete('/transcription-providers/{id}', [TranscriptionProviderController::class, 'destroy']);

    Route::get('/transcriptions', [TranscriptionController::class, 'index']);
    Route::post('/transcriptions', [TranscriptionController::class, 'store']);
    Route::get('/transcriptions/{id}', [TranscriptionController::class, 'show']);

    Route::post('/import', [ImportController::class, 'store']);

    Route::post('/update-solr', [SolrController::class, 'update']);
    Route::get('/update-solr', [SolrController::class, 'update']);
});
