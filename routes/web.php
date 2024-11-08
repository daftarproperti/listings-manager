<?php

use App\Http\Controllers\Admin\AiReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoogleLoginController;
use App\Http\Controllers\Admin\ListingsController as AdminListingsController;
use App\Http\Controllers\Admin\ListingsWithAttentionController as AdminListingsWithAttentionController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\ClosingsController as AdminClosingsController;
use App\Http\Controllers\Admin\CancelController as AdminCancelController;
use App\Http\Controllers\Admin\ExpiredListingsController as AdminExpiredListingsController;
use App\Http\Controllers\BlockchainInfoController;
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

// While the blockchain contract is still in alpha stage, we move to different contract addresses frequently.
// This endpoint shows the current blockchain contract address and version that is synced from Daftar Properti data.
Route::get('/_blockchain', [BlockchainInfoController::class, 'index']);

Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['auth:admin']], function () {
        Route::group(['prefix' => 'members', 'as' => 'members.'], function () {
            Route::get('/', [MembersController::class, 'index'])->name('index');
            Route::get('/search', [MembersController::class, 'search'])->name('search');
            Route::get('/{member}', [MembersController::class, 'show'])->name('show');
            Route::put('/{member}', [MembersController::class, 'update'])->name('update');
        });

        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [GoogleLoginController::class, 'handleLogout'])->name('logout');

        Route::group(['prefix' => 'listings', 'as' => 'listing.'], function () {
            Route::get('/', [AdminListingsController::class, 'index'])->name('index');
            Route::get('/{listing}', [AdminListingsController::class, 'show'])->name('show');
            Route::put('/{listing}', [AdminListingsController::class, 'update'])->name('update');

            Route::group(['prefix' => '{listing}/ai-review', 'as' => 'ai-review.'], function () {
                Route::post('/', [AiReviewController::class, 'doReview'])->name('review');
                Route::get('/', [AiReviewController::class, 'getReview'])->name('detail');
            });

            Route::group(['prefix' => '{listing}/remove-attention'], function () {
                Route::delete('/', [AdminListingsController::class, 'removeAttention'])->name('removeAttention');
            });
        });

        Route::group(['prefix' => 'listingsWithAttention', 'as' => 'listingsWithAttention.'], function () {
            Route::get('/', [AdminListingsWithAttentionController::class, 'index'])->name('index');
        });

        Route::group(['prefix' => 'closings', 'as' => 'closing.'], function () {
            Route::get('/', [AdminClosingsController::class, 'index'])->name('index');
            Route::get('/{closing}', [AdminClosingsController::class, 'show'])->name('show');
            Route::post('/{closing}', [AdminClosingsController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'cancel', 'as' => 'cancel.'], function () {
            Route::get('/', [AdminCancelController::class, 'index'])->name('index');
            Route::get('/{listing}', [AdminCancelController::class, 'show'])->name('show');
            Route::put('/{listing}', [AdminCancelController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'expired', 'as' => 'expired.'], function () {
            Route::get('/', [AdminExpiredListingsController::class, 'index'])->name('index');
        });
    });

    Route::group(['middleware' => ['guest:admin']], function () {
        Route::get('/', [DashboardController::class, 'home'])->name('home');
        Route::get('/login/google', [GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/login/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);
    });
});

Route::get('/version', [VersionController::class, 'index']);

// Implicitly bind ID with corresponding model
// Ref: https://laravel.com/docs/10.x/routing#implicit-binding
Route::group(['prefix' => 'public'], function () {
    Route::get('/agents/{user}', [AgentsController::class, 'detail']);
    // {listingById} is bound by listingId in RouteServiceProvider.
    Route::get('/listings/{listingById}', [ListingsController::class, 'detail']);
});

Route::group(['middleware' => ['auth-dev']], function () {
    Route::get('/', [HomeController::class, 'index']);
});

Route::get('/privasi', function () {
    return view('privacy');
});
Route::get('/syarat-ketentuan', function () {
    return view('terms-of-service');
});
Route::get('/peraturan', function () {
    return view('rules');
});
Route::get('/checklist', function () {
    return view('checklist');
});
Route::get('/whitepaper', function () {
    return view('whitepaper');
});
Route::get('/for-marketers', function () {
    return view('for-marketers');
});
