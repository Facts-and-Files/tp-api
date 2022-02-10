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

Route::apiResource('htrdata', HtrDataController::class);
Route::get('/htrdata/byitemid/{item_id}', [HtrDataController::class, 'showByItemId']);
Route::get('/htrdata/byprocessid/{process_id}', [HtrDataController::class, 'showByProcessId']);
