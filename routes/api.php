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

        Route::prefix('shipping')->group(function (){ // Shipping API Endpoints
            Route::prefix('country')->group(function () { // Country Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\ShippingController@store');
                Route::get('/', 'App\Http\Controllers\ShippingController@list');
                Route::get('/latest', 'App\Http\Controllers\ShippingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\ShippingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\ShippingController@delete');
            });
            Route::prefix('state')->group(function () { // State Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\StateShippingController@store');
                Route::get('/', 'App\Http\Controllers\StateShippingController@list');
                Route::get('/latest', 'App\Http\Controllers\StateShippingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\StateShippingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\StateShippingController@delete');
            });
            Route::prefix('city')->group(function () { // City Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\CityShippingController@store');
                Route::get('/', 'App\Http\Controllers\CityShippingController@list');
                Route::get('/latest', 'App\Http\Controllers\CityShippingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\CityShippingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\CityShippingController@delete');
            });
            Route::prefix('othercountry')->group(function () { // Other Country Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\OtherCountryShippingController@store');
                Route::get('/', 'App\Http\Controllers\OtherCountryShippingController@list');
                Route::get('/latest', 'App\Http\Controllers\OtherCountryShippingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\OtherCountryShippingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\OtherCountryShippingController@delete');
            });
            Route::prefix('otherstate')->group(function () { // Other State Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\OtherStateShippingController@store');
                Route::get('/', 'App\Http\Controllers\OtherStateShippingController@list');
                Route::get('/latest', 'App\Http\Controllers\OtherStateShippingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\OtherStateShippingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\OtherStateShippingController@delete');
            });
            Route::prefix('othercity')->group(function () { // Other State Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\OtherCityShippingController@store');
                Route::get('/', 'App\Http\Controllers\OtherCityShippingController@list');
                Route::get('/latest', 'App\Http\Controllers\OtherCityShippingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\OtherCityShippingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\OtherCityShippingController@delete');
            });
            Route::prefix('mastershipping')->group(function () { // Master Setting Shipping API Endpoints
                Route::post('/', 'App\Http\Controllers\MasterShippingSettingController@store');
                Route::get('/{id}', 'App\Http\Controllers\MasterShippingSettingController@retrieve');
                Route::patch('/{id}', 'App\Http\Controllers\MasterShippingSettingController@update');
                Route::delete('/{id}', 'App\Http\Controllers\MasterShippingSettingController@delete');
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

    Route::prefix('shipping')->group(function (){ // Shipping API Endpoints
        Route::prefix('country')->group(function () { // Country Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\ShippingController@store');
            Route::get('/', 'App\Http\Controllers\ShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\ShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\ShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\ShippingController@delete');
        });
        Route::prefix('state')->group(function () { // State Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\StateShippingController@store');
            Route::get('/', 'App\Http\Controllers\StateShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\StateShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\StateShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\StateShippingController@delete');
        });
        Route::prefix('city')->group(function () { // City Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\CityShippingController@store');
            Route::get('/', 'App\Http\Controllers\CityShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\CityShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\CityShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\CityShippingController@delete');
        });
        Route::prefix('othercountry')->group(function () { // Other Country Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\OtherCountryShippingController@store');
            Route::get('/', 'App\Http\Controllers\OtherCountryShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\OtherCountryShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\OtherCountryShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\OtherCountryShippingController@delete');
        });
        Route::prefix('otherstate')->group(function () { // Other State Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\OtherStateShippingController@store');
            Route::get('/', 'App\Http\Controllers\OtherStateShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\OtherStateShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\OtherStateShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\OtherStateShippingController@delete');
        });
        Route::prefix('othercity')->group(function () { // Other State Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\OtherCityShippingController@store');
            Route::get('/', 'App\Http\Controllers\OtherCityShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\OtherCityShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\OtherCityShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\OtherCityShippingController@delete');
        });
        Route::prefix('mastershipping')->group(function () { // Master Setting Shipping API Endpoints
            Route::post('/', 'App\Http\Controllers\MasterShippingSettingController@store');
            Route::get('/{id}', 'App\Http\Controllers\MasterShippingSettingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\MasterShippingSettingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\MasterShippingSettingController@delete');
        });
  
    });
});
