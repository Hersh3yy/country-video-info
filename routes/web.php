<?php

use App\Http\Controllers\CountryVideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::get('/country-videos', [CountryVideoController::class, 'index']);
});
