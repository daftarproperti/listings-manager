<?php

use Illuminate\Support\Facades\Route;

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
    Route::post('{secret_token}/telegram', 'Api\WebhookController@receiveTelegramMessage')->middleware(['telegram-webhook']);
    Route::post('tasks/gpt', 'Api\WebhookController@processGpt')->middleware('queue-webhook');
});

Route::group(['prefix' => 'tele-app', 'middleware' => ['telegram-app']], function () {
    Route::prefix('properties')->group(function () {
        Route::get('/', 'Api\PropertiesController@index');
        Route::get('/{id}', 'Api\PropertiesController@show');
    });
    Route::get('photo/{fileId}/{fileUniqueId}', 'Api\PhotoController@telegramPhoto')->name('telegram-photo');
});
