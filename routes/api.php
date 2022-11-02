<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () { // Admin only routes
    Route::prefix('v1')->group(function () { // API v1 Endpoints

        Route::prefix('users')->group(function () { // User API Endpoints
            Route::get('me', function (Request $request) {
                return $request->user();
            });
        });

        Route::prefix('items')->group(function () { // Item API Endpoints
            Route::post('/', 'App\Http\Controllers\ItemController@store');
            Route::post('/duplicate/{itemId}', 'App\Http\Controllers\ItemController@duplicate');
            Route::patch('/{itemId}', 'App\Http\Controllers\ItemController@update');
            Route::delete('/{itemId}', 'App\Http\Controllers\ItemController@delete');
        });

        Route::prefix('categories')->group(function () { // Category API Endpoints
            Route::post('/', 'App\Http\Controllers\CategoryController@store');
            Route::post('/move', 'App\Http\Controllers\CategoryController@move');
            Route::patch('/{categoryId}', 'App\Http\Controllers\CategoryController@update');
            Route::delete('/{categoryId}', 'App\Http\Controllers\CategoryController@delete');
        });

        Route::prefix('orders')->group(function () {
            Route::prefix('shipping-notes')->group(function () {
                Route::patch('update', 'App\Http\Controllers\OrderController@updateShippingOptions');
            });
        });
    });
});

Route::prefix('v1')->group(function () { // API v1 Endpoints
    Route::prefix('items')->group(function () { // Item API Endpoints
        Route::get('/', 'App\Http\Controllers\ItemController@list');
        Route::get('/{itemId}', 'App\Http\Controllers\ItemController@retrieve');
    });

    Route::prefix('categories')->group(function () { // Category API Endpoints
        Route::get('/', 'App\Http\Controllers\CategoryController@list');
    });

    Route::prefix('tags')->group(function () { // Tag API Endpoints
        Route::get('/', 'App\Http\Controllers\TagController@list');
    });

    Route::prefix('contact')->group(function () { // Contact API Endpoints
        Route::post('/send-message', 'App\Http\Controllers\ContactController@sendMessage');
    });

    Route::prefix('orders')->group(function () { // Order API Endpoints
        Route::post('/checkout', 'App\Http\Controllers\OrderController@checkout');
        Route::post('/verify', 'App\Http\Controllers\OrderController@verify');

        Route::prefix('shipping-notes')->group(function () {
            Route::get('/', 'App\Http\Controllers\OrderController@retrieveShippingOptions');
        });
    });
});
