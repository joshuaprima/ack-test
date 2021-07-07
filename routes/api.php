<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:users')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
});

Route::group(['middleware' => 'auth:users'], function () {
    Route::get('/profile', [\App\Http\Controllers\Api\UserController::class, 'getUserProfile']);
    Route::post('/edit-profile', [\App\Http\Controllers\Api\UserController::class, 'editProfile']);
    Route::post('/create-toko', [\App\Http\Controllers\Api\TokoController::class, 'createToko']);
    Route::get('/detail-toko/{id}', [\App\Http\Controllers\Api\TokoController::class, 'getDetailToko']);
    Route::get('/list-toko', [\App\Http\Controllers\Api\TokoController::class, 'getListToko']);
    Route::post('/edit-toko/{id}', [\App\Http\Controllers\Api\TokoController::class, 'editToko']);
});
