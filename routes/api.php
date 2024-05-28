<?php

use App\Http\Controllers\Api\ListingsController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DevController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\PropertiesController;
use App\Http\Controllers\Api\SavedSearchController;
use App\Http\Controllers\Api\TelegramUserController;
use App\Http\Controllers\Api\UserController;
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

if (App::environment('development')) {
    Route::prefix('_dev')->group(function () {
        Route::post('/queue', [DevController::class, 'queue']);
    });
}

Route::prefix('webhook')->group(function () {
    Route::post('{secret_token}/telegram', [WebhookController::class, 'receiveTelegramMessage'])
        ->middleware(['telegram-webhook'])
        ->name('telegram-webhook');
});

Route::group(['prefix' => 'tele-app', 'middleware' => ['telegram-app']], function () {
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'index']);
        Route::get('/{property}', [PropertiesController::class, 'show']);
    });

    Route::prefix('listings')->group(function () {
        Route::get('/', [ListingsController::class, 'index']);
        Route::post('/', [ListingsController::class, 'create']);
        Route::get('/{listing}', [ListingsController::class, 'show']);
        Route::post('/{listing}', [ListingsController::class, 'update'])->middleware('listing-user');
        Route::delete('/{listing}', [ListingsController::class, 'delete'])->middleware('listing-user');
    });

    Route::prefix('saved-searches')->group(function () {
        Route::get('/', [SavedSearchController::class, 'index']);
        Route::post('/', [SavedSearchController::class, 'create']);
        Route::get('/{savedSearch}', [SavedSearchController::class, 'show']);
        Route::post('/{savedSearch}', [SavedSearchController::class, 'update']);
        Route::delete('/{savedSearch}', [SavedSearchController::class, 'delete']);
    });

    // Temporarily let TelegramUserController stay to not break tests.
    // TODO: Remove when everyone has migrated to User model instead of TelegramUser.
    Route::prefix('telegram-users')->group(function () {
        Route::get('/profile', [TelegramUserController::class, 'profile']);
        Route::post('/profile', [TelegramUserController::class, 'updateProfile']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/profile', [UserController::class, 'updateProfile']);
    });

    Route::post('upload/image', [PhotoController::class, 'uploadImage']);

    Route::prefix('cities')->group(function () {
        Route::get('/', [CityController::class, 'index']);
        Route::get('/{id}', [CityController::class, 'getCityById']);
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOTP'])->middleware('throttle-otp-request:phoneNumber');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->middleware('throttle-otp-request:token');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('telegram-app');
});

Route::get('photo/{fileId}/{fileUniqueId}', [PhotoController::class, 'telegramPhoto'])->name('telegram-photo');
