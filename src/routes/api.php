<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HtrDataController;
use App\Http\Controllers\ItemController;

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

    Route::get('/users/{id}/htrdata', [HtrDataController::class, 'showByUserId']);
});
