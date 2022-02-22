<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HtrDataController;

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
    Route::get('/htrdata/{item_id}', [HtrDataController::class, 'show']);
    Route::put('/htrdata/{item_id}', [HtrDataController::class, 'update']);
    Route::delete('/htrdata/{item_id}', [HtrDataController::class, 'destroy']);
    Route::get('/htrdata/byprocessid/{process_id}', [HtrDataController::class, 'showByProcessId']);
});
