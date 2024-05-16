<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoogleLoginController;
use App\Http\Controllers\Admin\ListingsController as AdminListingsController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\TelegramController;
use App\Http\Controllers\QueueSizeController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\Web\Public\AgentsController;
use App\Http\Controllers\Web\Public\ListingsController;
use App\Http\Controllers\Web\Public\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Inspect queue size.
// Useful to confirm whether it's okay to stop the current version.
// (Versions with non-empty queue should not be stopped yet even if they stop receiving traffic).
Route::get('/_qs', [QueueSizeController::class, 'index']);

Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['auth:admin']], function () {
        Route::get('/members', [MembersController::class, 'index'])->name('members');
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [GoogleLoginController::class, 'handleLogout'])->name('logout');

        Route::group(['prefix' => 'telegram', 'as' => 'telegram.'], function () {
            Route::get('/allowlists', [TelegramController::class, 'allowlistIndex'])->name('allowlists');
            Route::get('/allowlists/{allowlist}', [TelegramController::class, 'allowlistDetail'])->name('allowlists.detail');
            Route::post('/allowlists/{allowlist}', [TelegramController::class, 'allowlistUpdate'])->name('allowlists.update');
        });

        Route::group(['prefix' => 'listings', 'as' => 'listing.'], function () {
            Route::get('/', [AdminListingsController::class, 'index'])->name('index');
            Route::get('/{listing}', [AdminListingsController::class, 'show'])->name('show');
        });
    });

    Route::group(['middleware' => ['guest:admin']], function () {
        Route::get('/', [DashboardController::class, 'home'])->name('home');
        Route::get('/login/google', [GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/login/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);
    });
});

Route::get('/version', [VersionController::class, 'index']);

Route::group(['middleware' => ['auth-dev']], function () {
    // Implicitly bind ID with corresponding model
    // Ref: https://laravel.com/docs/10.x/routing#implicit-binding
    Route::group(['prefix' => 'public'], function () {
        Route::get('/agents/{telegramUser}', [AgentsController::class, 'detail']);
        Route::get('/listings/{listing}', [ListingsController::class, 'detail']);
    });

    Route::get('/', [HomeController::class, 'index']);
});
