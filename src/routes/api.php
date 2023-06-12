<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HtrDataController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\AutoEnrichmentController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DatasetController;

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

Route::middleware(['auth:api'])->group(function() {
    Route::get('/htrdata', [HtrDataController::class, 'index']);
    Route::post('/htrdata', [HtrDataController::class, 'store']);
    Route::get('/htrdata/{id}', [HtrDataController::class, 'show']);
    Route::put('/htrdata/{id}', [HtrDataController::class, 'update']);
    Route::delete('/htrdata/{id}', [HtrDataController::class, 'destroy']);

    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::get('/items/{id}/htrdata', [HtrDataController::class, 'showByItemId']);
    Route::get('/items/{id}/autoenrichments', [AutoEnrichmentController::class, 'showByItemId']);

    Route::get('/users/{id}/htrdata', [HtrDataController::class, 'showByUserId']);

    Route::get('/stories', [StoryController::class, 'index']);
    Route::get('/stories/{id}', [StoryController::class, 'show']);
    Route::get('/stories/{id}/autoenrichments', [AutoEnrichmentController::class, 'showByStoryId']);
    Route::put('/stories/{id}', [StoryController::class, 'update']);
    Route::get('/stories/{id}/campaigns', [StoryController::class, 'showCampaigns']);
    Route::put('/stories/{id}/campaigns', [StoryController::class, 'updateCampaigns']);

    Route::get('/autoenrichments', [AutoEnrichmentController::class, 'index']);
    Route::post('/autoenrichments', [AutoEnrichmentController::class, 'store']);
    Route::post('/autoenrichments/bulkimports', [AutoEnrichmentController::class, 'storeBulk']);
    Route::get('/autoenrichments/{id}', [AutoEnrichmentController::class, 'show']);
    Route::put('/autoenrichments/{id}', [AutoEnrichmentController::class, 'update']);
    Route::delete('/autoenrichments/{id}', [AutoEnrichmentController::class, 'destroy']);

    Route::get('/languages', [LanguageController::class, 'index']);
    Route::get('/languages/{id}', [LanguageController::class, 'show']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{id}', [TeamController::class, 'show']);
    Route::put('/teams/{id}', [TeamController::class, 'update']);
    Route::delete('/teams/{id}', [TeamController::class, 'destroy']);

    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);

    Route::get('/datasets', [DatasetController::class, 'index']);
    Route::get('/datasets/{id}', [DatasetController::class, 'show']);
});
