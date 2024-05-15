<?php

use App\Http\Controllers\Web\Public\TestController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

if (App::environment('development', 'local')) {
    Route::get('/view-test', [TestController::class, 'index']);
    Route::get('/view-test/{page}', [TestController::class, 'page']);
}
