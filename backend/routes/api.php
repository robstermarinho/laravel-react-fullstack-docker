<?php


use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;


Route::get('/health', [ApiController::class, 'health']);
