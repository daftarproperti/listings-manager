<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoogleLoginController;
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

Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['auth:admin']], function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [GoogleLoginController::class, 'handleLogout'])->name('logout');
    });

    Route::group(['middleware' => ['guest:admin']], function () {
        Route::get('/', [DashboardController::class, 'home'])->name('home');
        Route::get('/login/google', [GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/login/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);
    });
});
