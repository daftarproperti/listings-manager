<?php

use App\Http\Controllers\Api\ListingsController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ClosingsController;
use App\Http\Controllers\Api\DevController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\PropertiesController;
use App\Http\Controllers\Api\SavedSearchController;
use App\Http\Controllers\Api\UserController;

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

Route::group(['prefix' => 'app', 'middleware' => ['dp-app']], function () {
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'index']);
        Route::get('/{property}', [PropertiesController::class, 'show']);
    });

    Route::prefix('listings')->group(function () {
        Route::get('/', [ListingsController::class, 'index']);
        Route::post('/', [ListingsController::class, 'create']);
        Route::get('/{listing}', [ListingsController::class, 'show']);
        Route::post('/generate-from-text', [ListingsController::class, 'generateFromText']);
        Route::post('/get-generate-result', [ListingsController::class, 'getGenerateResult']);
        Route::post('/{listing}/likely-connected', [ListingsController::class, 'getLikelyConnected']);
        Route::post('/{listing}', [ListingsController::class, 'update'])->middleware('listing-user');
        Route::delete('/{listing}', [ListingsController::class, 'delete'])->middleware('listing-user');
        Route::post('/{listing}/closings', [ClosingsController::class, 'closing'])->middleware('listing-user');
        Route::put('/{listing}/cancel', [ListingsController::class, 'updateCancellationNote'])->middleware('listing-user');

    });

    Route::prefix('saved-searches')->group(function () {
        Route::get('/', [SavedSearchController::class, 'index']);
        Route::post('/', [SavedSearchController::class, 'create']);
        Route::get('/{savedSearch}', [SavedSearchController::class, 'show']);
        Route::post('/{savedSearch}', [SavedSearchController::class, 'update']);
        Route::delete('/{savedSearch}', [SavedSearchController::class, 'delete']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/profile', [UserController::class, 'updateProfile']);
        Route::post('/secret-key', [UserController::class, 'generateSecretKey']);
        Route::delete('/secret-key', [UserController::class, 'deleteSecretKey']);
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
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('dp-app');

    Route::post('/impersonate', [AuthController::class, 'impersonate'])->middleware('impersonate');

    Route::post('/verify-totp', [AuthController::class, 'verifyTOTP'])->middleware('throttle-otp-request:phoneNumber');
});

Route::get('photo/{fileId}/{fileUniqueId}', [PhotoController::class, 'telegramPhoto'])->name('telegram-photo');
