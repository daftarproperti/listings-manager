<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\PropertiesController;
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
    Route::post('{secret_token}/telegram', [WebhookController::class, 'receiveTelegramMessage'])->middleware(['telegram-webhook']);
    Route::post('tasks/gpt', [WebhookController::class, 'processGpt'])->middleware('queue-webhook');
});

Route::group(['prefix' => 'tele-app', 'middleware' => ['telegram-app']], function () {
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'index']);
        Route::post('/', [PropertiesController::class, 'create']);
        Route::get('/{property}', [PropertiesController::class, 'show']);
        Route::post('/{property}', [PropertiesController::class, 'update'])->middleware('property-user');
        Route::delete('/{property}', [PropertiesController::class, 'delete'])->middleware('property-user');
    });
});

Route::get('photo/{fileId}/{fileUniqueId}', [PhotoController::class, 'telegramPhoto'])->name('telegram-photo');
