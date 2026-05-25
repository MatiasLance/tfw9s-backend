<?php

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

Route::prefix('api')->group(function() {

    Route::prefix('v1')->group(function () { // Api v1
        Route::prefix('auth')->group(function () {
            Route::post('login', 'App\Http\Controllers\AuthController@authenticate')->name('login');
            Route::post('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');
            Route::post('forgot-password', 'App\Http\Controllers\AuthController@forgotPassword')->name('forgotPassword')->middleware('throttle:forgot-password');
            Route::post('reset-password', 'App\Http\Controllers\AuthController@resetPassword')->name('resetPassword');
        });
    });

});
