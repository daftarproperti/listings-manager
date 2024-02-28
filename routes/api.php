<?php

use App\Http\Controllers\Api\ListingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\PropertiesController;
use App\Http\Controllers\Api\TelegramUserController;
use App\Http\Controllers\Api\WebhookController;

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

Route::prefix('webhook')->group(function () {
    Route::post('{secret_token}/telegram', [WebhookController::class, 'receiveTelegramMessage'])
        ->middleware(['telegram-webhook'])
        ->name('telegram-webhook');
});

Route::group(['prefix' => 'tele-app', 'middleware' => ['telegram-app']], function () {
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'index']);
        Route::post('/', [PropertiesController::class, 'create']);
        Route::get('/{property}', [PropertiesController::class, 'show']);
        Route::post('/{property}', [PropertiesController::class, 'update'])->middleware('property-user');
        Route::delete('/{property}', [PropertiesController::class, 'delete'])->middleware('property-user');
    });

    Route::prefix('listings')->group(function () {
        Route::get('/', [ListingsController::class, 'index']);
        Route::post('/', [ListingsController::class, 'create']);
        Route::get('/{listing}', [ListingsController::class, 'show']);
        Route::post('/{listing}', [ListingsController::class, 'update'])->middleware('listing-user');
        Route::delete('/{listing}', [ListingsController::class, 'delete'])->middleware('listing-user');
    });

    Route::prefix('users')->group(function () {
        Route::get('/profile', [TelegramUserController::class, 'profile']);
        Route::post('/profile', [TelegramUserController::class, 'updateProfile']);
    });
});

Route::get('photo/{fileId}/{fileUniqueId}', [PhotoController::class, 'telegramPhoto'])->name('telegram-photo');
