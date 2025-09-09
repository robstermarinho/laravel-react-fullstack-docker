<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cache testing routes
    Route::post('/test-cache', [AuthController::class, 'testCache']);
    Route::get('/cache-stats', [AuthController::class, 'getCacheStats']);
    Route::delete('/clear-cache', [AuthController::class, 'clearTestCache']);
});

Route::get('/health', [ApiController::class, 'health']);
Route::post('/test-job', [ApiController::class, 'dispatchTestJob']);
