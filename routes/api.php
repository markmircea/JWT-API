<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIAuthController;
use App\Http\Controllers\QuotationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



    Route::post('auth/login', [APIAuthController::class, 'login']);
    Route::post('auth/blacklistAccess', [APIAuthController::class, 'blacklistAccessToken']);
    Route::post('auth/blacklistRefresh', [APIAuthController::class, 'blacklistRefreshToken']);
    Route::post('auth/refresh', [APIAuthController::class, 'refresh']);


    Route::post('quotation', [QuotationController::class, 'getQuotation'])->middleware('jwt.auth');



