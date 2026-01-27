<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ToggleTaxControlController;
use App\Http\Controllers\PaymentSettingController;

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

        Route::prefix('users')->group(function () {
            Route::get('me', function (Request $request) {
                return $request->user();
            });
        });

        Route::prefix('items')->group(function () {
            Route::post('/', 'App\Http\Controllers\ItemController@store');
            Route::post('/duplicate/{itemId}', 'App\Http\Controllers\ItemController@duplicate');
            Route::patch('/addVariant/{itemId}', 'App\Http\Controllers\ItemController@storeItemVariant');
            Route::patch('/{itemId}', 'App\Http\Controllers\ItemController@update');
            Route::delete('/{itemId}', 'App\Http\Controllers\ItemController@delete');
        });

        Route::prefix('categories')->group(function () { 
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

        Route::prefix("tax")->group(function() {
            Route::get('/', 'App\Http\Controllers\TaxController@list');
            Route::post('/{id}', 'App\Http\Controllers\TaxController@update');
        });

        Route::prefix("toggletax")->group(function() {
            Route::get('/', 'App\Http\Controllers\ToggleTaxControlController@list');
            Route::post('/{id}', 'App\Http\Controllers\ToggleTaxControlController@update');
        });

        Route::prefix("regions")->group(function() {
            Route::post('/', 'App\Http\Controllers\RegionController@store');
            Route::post('/{id}', 'App\Http\Controllers\RegionController@update');
            Route::delete('/{id}', 'App\Http\Controllers\RegionController@delete');
        });

        Route::prefix("fields")->group(function() {
            Route::post('/', 'App\Http\Controllers\FieldController@store');
            Route::post('/{id}', 'App\Http\Controllers\FieldController@update');
            Route::delete('/{id}', 'App\Http\Controllers\FieldController@delete');
        });

        Route::prefix("teams")->group(function() {
            Route::post('/', 'App\Http\Controllers\TeamController@store');
            Route::post('/{id}', 'App\Http\Controllers\TeamController@update');
            Route::delete('/{id}', 'App\Http\Controllers\TeamController@delete');
        });

        Route::prefix("events")->group(function() {
            Route::post('/', 'App\Http\Controllers\EventController@store');
            Route::post('/{id}', 'App\Http\Controllers\EventController@update');
            Route::delete('/{id}', 'App\Http\Controllers\EventController@delete');
        });

        Route::prefix("agegroups")->group(function() {
            Route::post('/', 'App\Http\Controllers\AgeGroupController@store');
            Route::post('/{id}', 'App\Http\Controllers\AgeGroupController@update');
            Route::delete('/{id}', 'App\Http\Controllers\AgeGroupController@delete');
        });

        Route::prefix("partnersponsors")->group(function() {
            Route::post('/', 'App\Http\Controllers\PartnerSponsorController@store');
            Route::post('/{id}', 'App\Http\Controllers\PartnerSponsorController@update');
            Route::delete('/{id}', 'App\Http\Controllers\PartnerSponsorController@delete');
        });

        Route::prefix("payment")->group(function() {
            Route::prefix("setting")->group(function() {
                Route::get("/", 'App\Http\Controllers\PaymentSettingController@list');
                Route::patch("/{id}", 'App\Http\Controllers\PaymentSettingController@update');
            });
        });
    });
});

Route::prefix('v1')->group(function () {
    Route::prefix('transaction')->group(function () {
        Route::get('/retrieve/{key}', 'App\Http\Controllers\TransactionController@retrieve');
        Route::post('/generate', 'App\Http\Controllers\TransactionController@generate');
        Route::post('/savemedia', 'App\Http\Controllers\TransactionController@saveMedia');
    });

    Route::prefix('items')->group(function () {
        Route::get('/', 'App\Http\Controllers\ItemController@list');
        Route::get('/{itemId}', 'App\Http\Controllers\ItemController@retrieve');
        Route::post('/{id}', 'App\Http\Controllers\ItemController@update');
        Route::delete('/{id}', 'App\Http\Controllers\ItemController@delete');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', 'App\Http\Controllers\CategoryController@list');
    });

    Route::prefix('tags')->group(function () {
        Route::get('/', 'App\Http\Controllers\TagController@list');
    });

    Route::prefix('contact')->group(function () {
        Route::post('/send-message', 'App\Http\Controllers\ContactController@sendMessage');
    });

    Route::prefix('orders')->group(function () {
        Route::post('/checkout', 'App\Http\Controllers\OrderController@checkout');
        Route::post('/verify', 'App\Http\Controllers\OrderController@verify');
        Route::post('/calculation', 'App\Http\Controllers\OrderController@shippingCalc');

        Route::prefix('shipping-notes')->group(function () {
            Route::get('/', 'App\Http\Controllers\OrderController@retrieveShippingOptions');
        });
    });

    Route::prefix('tournament')->group(function () {
        Route::post('/indiv/checkout', 'App\Http\Controllers\IndividualRegistrationController@checkout');
        Route::post('/team/checkout', 'App\Http\Controllers\TeamRegistrationController@checkout');
        Route::post('/indiv/verify', 'App\Http\Controllers\IndividualRegistrationController@verify');
        Route::post('/team/verify', 'App\Http\Controllers\TeamRegistrationController@verify');
        Route::post('/indiv/stripe/calculation', 'App\Http\Controllers\IndividualRegistrationController@initialStripeCalculation')
             ->middleware(['throttle:calculations']);
        Route::post('/indiv/afterpay/calculation', 'App\Http\Controllers\IndividualRegistrationController@initialAfterPayCalculation')
             ->middleware(['throttle:calculations']);
        Route::post('/team/stripe/calculation', 'App\Http\Controllers\TeamRegistrationController@initialStripeCalculation')
             ->middleware(['throttle:calculations']);
        Route::post('/team/afterpay/calculation', 'App\Http\Controllers\TeamRegistrationController@initialAfterPayCalculation')
             ->middleware(['throttle:calculations']);
    });

    Route::prefix('discountcode')->group(function() {
        Route::get('/', 'App\Http\Controllers\DiscountCodeController@list');
        Route::get('/{id}', 'App\Http\Controllers\DiscountCodeController@retrieve');
        Route::post('/', 'App\Http\Controllers\DiscountCodeController@store');
        Route::post('/verifycode', 'App\Http\Controllers\DiscountCodeController@discountCodeCheck');
        Route::delete('/{id}', 'App\Http\Controllers\DiscountCodeController@delete');
        Route::patch('/{id}', 'App\Http\Controllers\DiscountCodeController@update');
    });

    Route::prefix("tax")->group(function() {
        Route::get('/', 'App\Http\Controllers\TaxController@list');
    });

    Route::prefix("toggletax")->group(function() {
        Route::get('/', 'App\Http\Controllers\ToggleTaxControlController@list');
    });

    Route::prefix("regions")->group(function() {
        Route::get('/', 'App\Http\Controllers\RegionController@list');
        Route::get('/all', 'App\Http\Controllers\RegionController@all');
        Route::post('/', 'App\Http\Controllers\RegionController@store');
        Route::post('/update/{id}', 'App\Http\Controllers\RegionController@update');
        Route::get('/{id}', 'App\Http\Controllers\RegionController@retrieve');
        Route::post('/', 'App\Http\Controllers\RegionController@store');
        Route::post('/{id}', 'App\Http\Controllers\RegionController@update');
        Route::delete('/{id}', 'App\Http\Controllers\RegionController@delete');

    });

    Route::prefix("fields")->group(function() {
        Route::get('/', 'App\Http\Controllers\FieldController@list');
        Route::get('/all', 'App\Http\Controllers\FieldController@all');
        Route::get('/{id}', 'App\Http\Controllers\FieldController@retrieve');
        Route::post('/', 'App\Http\Controllers\FieldController@store');
        Route::post('/{id}', 'App\Http\Controllers\FieldController@update');
        Route::delete('/{id}', 'App\Http\Controllers\FieldController@delete');

    });

    Route::prefix("agegroups")->group(function() {
        Route::get('/', 'App\Http\Controllers\AgeGroupController@list');
        Route::get('/all', 'App\Http\Controllers\AgeGroupController@all');
        Route::get('/{id}', 'App\Http\Controllers\AgeGroupController@retrieve');
        Route::post('/', 'App\Http\Controllers\AgeGroupController@store');
        Route::post('/{id}', 'App\Http\Controllers\AgeGroupController@update');
        Route::delete('/{id}', 'App\Http\Controllers\AgeGroupController@delete');
    });

    Route::prefix("managers")->group(function() {
        Route::get('/', 'App\Http\Controllers\ManagerController@list');
        Route::get('/{id}', 'App\Http\Controllers\ManagerController@retrieve');
        Route::post('/', 'App\Http\Controllers\ManagerController@store');
        Route::post('/{id}', 'App\Http\Controllers\ManagerController@update');
        Route::delete('/{id}', 'App\Http\Controllers\ManagerController@delete');

    });

    Route::prefix("teams")->group(function() {
        Route::get('/', 'App\Http\Controllers\TeamController@list');
        Route::get('/all', 'App\Http\Controllers\TeamController@all');
        Route::get('/trashed', 'App\Http\Controllers\TeamController@trashed');
        Route::get('/{id}', 'App\Http\Controllers\TeamController@retrieve');
        Route::get('link/{id}', 'App\Http\Controllers\TeamController@generateTeamAndIndividualRegistrationLink');
        Route::prefix("player")->group(function() {
            Route::prefix("registration")->group(function() {
                Route::prefix("link")->group(function() {
                    Route::get('/{id}', 'App\Http\Controllers\TeamController@generatePlayerRegistrationLink');
                });
            });
        });
        Route::post('/', 'App\Http\Controllers\TeamController@store');
        Route::post('/{id}', 'App\Http\Controllers\TeamController@update');
        Route::delete('/{id}', 'App\Http\Controllers\TeamController@delete');
        Route::post('/refund/{id}', 'App\Http\Controllers\TeamController@refund');
        Route::post('/cancelref/{id}', 'App\Http\Controllers\TeamController@cancelref');
    });

    Route::prefix("events")->group(function() {
        Route::get('/', 'App\Http\Controllers\EventController@list');
        Route::get('/all', 'App\Http\Controllers\EventController@all');
        Route::get('/{id}', 'App\Http\Controllers\EventController@retrieve');
        Route::post('/', 'App\Http\Controllers\EventController@store');
        Route::post('/{id}', 'App\Http\Controllers\EventController@update');
        Route::delete('/{id}', 'App\Http\Controllers\EventController@delete');
    });

    Route::prefix("eventmatches")->group(function() {
        Route::get('/', 'App\Http\Controllers\EventMatchController@list');
        Route::get('/{id}', 'App\Http\Controllers\EventMatchController@retrieve');
        Route::post('/update/{id}', 'App\Http\Controllers\EventMatchController@updatescore');
        Route::post('/{id}', 'App\Http\Controllers\EventMatchController@storeResult');
        Route::post('/updateresult/{id}', 'App\Http\Controllers\EventMatchController@updatedResult');
    });

    Route::prefix("partnersponsors")->group(function() {
        Route::get('/', 'App\Http\Controllers\PartnerSponsorController@list');
        Route::get('/{id}', 'App\Http\Controllers\PartnerSponsorController@retrieve');
        Route::post('/', 'App\Http\Controllers\PartnerSponsorController@store');
        Route::post('/{id}', 'App\Http\Controllers\PartnerSponsorController@update');
        Route::delete('/{id}', 'App\Http\Controllers\PartnerSponsorController@delete');
    });

    Route::prefix("news")->group(function() {
        Route::get('/', 'App\Http\Controllers\NewsController@list');
        Route::get('/{id}', 'App\Http\Controllers\NewsController@retrieve');
        Route::post('/', 'App\Http\Controllers\NewsController@store');
        Route::post('/{id}', 'App\Http\Controllers\NewsController@update');
        Route::delete('/{id}', 'App\Http\Controllers\NewsController@delete');
    });

    Route::prefix("teampositions")->group(function() {
        Route::get('/', 'App\Http\Controllers\TeamPositionController@list');
        Route::get('/list', 'App\Http\Controllers\TeamPositionController@listOfTeamPositions');
        Route::get('/{id}', 'App\Http\Controllers\TeamPositionController@retrieve');
        Route::post('/', 'App\Http\Controllers\TeamPositionController@store');
        Route::post('/update', 'App\Http\Controllers\TeamPositionController@update');
        Route::delete('/{id}', 'App\Http\Controllers\TeamPositionController@delete');
    });

    Route::prefix("guidelines")->group(function() {
        Route::get('/', 'App\Http\Controllers\GuidelineController@list');
        Route::get('/{id}', 'App\Http\Controllers\GuidelineController@retrieve');
        Route::post('/', 'App\Http\Controllers\GuidelineController@store');
        Route::post('/{id}', 'App\Http\Controllers\GuidelineController@update');
        Route::post('/active/{id}', 'App\Http\Controllers\GuidelineController@setActive');
        Route::post('/deactivate/{id}', 'App\Http\Controllers\GuidelineController@deactivate');
        Route::delete('/{id}', 'App\Http\Controllers\GuidelineController@delete');

    });

    Route::prefix("series")->group(function() {
        Route::get('/', 'App\Http\Controllers\SeriesController@list');
        Route::prefix('paginated')->group(function() {
            Route::get('/', 'App\Http\Controllers\SeriesController@paginatedList');
        });
        Route::get('/', 'App\Http\Controllers\SeriesController@list');
        Route::get('/names', 'App\Http\Controllers\SeriesController@listOfSeriesName');
        Route::get('/{id}', 'App\Http\Controllers\SeriesController@retrieve');
        Route::get('/token/{key}', 'App\Http\Controllers\SeriesController@decrypt');
        Route::post('/', 'App\Http\Controllers\SeriesController@store');
        Route::post('/{id}', 'App\Http\Controllers\SeriesController@update');
        Route::delete('/{id}', 'App\Http\Controllers\SeriesController@delete');
        Route::post('/resume/{id}', 'App\Http\Controllers\SeriesController@resumeSeries');
        Route::post('/pause/{id}', 'App\Http\Controllers\SeriesController@pauseSeries');
        Route::post('/edit/thumbnail', 'App\Http\Controllers\SeriesController@editThumbnail');
        Route::post('/notify/{id}', 'App\Http\Controllers\SeriesController@sendRegistration');
        Route::post('/teamlinks/{id}', 'App\Http\Controllers\SeriesController@seriesTeamLinks');
    });

    Route::prefix("players")->group(function() {
        Route::get('/', 'App\Http\Controllers\PlayersController@list');
        Route::get('/trashed', 'App\Http\Controllers\PlayersController@trashed');
        Route::get('/{id}', 'App\Http\Controllers\PlayersController@retrieve');
        Route::post('/', 'App\Http\Controllers\PlayersController@store');
        Route::post('/{id}', 'App\Http\Controllers\PlayersController@update');
        Route::delete('/{id}', 'App\Http\Controllers\PlayersController@delete');
        Route::post('/refund/{id}', 'App\Http\Controllers\PlayersController@refund');
        Route::post('/cancelref/{id}', 'App\Http\Controllers\PlayersController@cancelref');
        Route::post('/savemedia/{id}', 'App\Http\Controllers\PlayersController@savemedia');
    });

    Route::prefix("teamlimit")->group(function() {
        Route::get('/{series_id}', 'App\Http\Controllers\TeamLimitController@list');
        Route::post('/update', 'App\Http\Controllers\TeamLimitController@update');
    });

    Route::prefix("teamfolder")->group(function() {
        Route::get('/{id}', 'App\Http\Controllers\TeamFolderController@retrieve');
        Route::post('/update/{id}', 'App\Http\Controllers\TeamFolderController@update');
    });

    Route::prefix("total")->group(function() {
        Route::get('/', 'App\Http\Controllers\TotalController@retrieve');
    });

    Route::prefix("variant")->group(function() {
        Route::get('/', 'App\Http\Controllers\VariantController@retrieve');
        Route::post('/', 'App\Http\Controllers\VariantController@store');
        Route::post('/itemvariant', 'App\Http\Controllers\VariantController@storeVariant');
        Route::get('/{id}', 'App\Http\Controllers\VariantController@itemvariant');
        Route::delete('/{id}', 'App\Http\Controllers\VariantController@delete');
    });

    Route::prefix("faq")->group(function() {
        Route::get('/', 'App\Http\Controllers\FaqController@list');
        Route::get('/{id}', 'App\Http\Controllers\FaqController@retrieve');
        Route::post('/', 'App\Http\Controllers\FaqController@store');
        Route::post('/{id}', 'App\Http\Controllers\FaqController@update');
        Route::delete('/{id}', 'App\Http\Controllers\FaqController@delete');
    });

    Route::prefix("homepageinfo")->group(function() {
        Route::get('/{id}', 'App\Http\Controllers\HomePageInformationController@retrieve');
        Route::post('/update/{id}', 'App\Http\Controllers\HomePageInformationController@update');
    });

    Route::prefix('shipping')->group(function (){
        Route::prefix('country')->group(function () {
            Route::post('/', 'App\Http\Controllers\ShippingController@store');
            Route::get('/', 'App\Http\Controllers\ShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\ShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\ShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\ShippingController@delete');
        });
        Route::prefix('state')->group(function () {
            Route::post('/', 'App\Http\Controllers\StateShippingController@store');
            Route::get('/', 'App\Http\Controllers\StateShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\StateShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\StateShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\StateShippingController@delete');
        });
        Route::prefix('city')->group(function () {
            Route::post('/', 'App\Http\Controllers\CityShippingController@store');
            Route::get('/', 'App\Http\Controllers\CityShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\CityShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\CityShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\CityShippingController@delete');
        });
        Route::prefix('othercountry')->group(function () {
            Route::post('/', 'App\Http\Controllers\OtherCountryShippingController@store');
            Route::get('/', 'App\Http\Controllers\OtherCountryShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\OtherCountryShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\OtherCountryShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\OtherCountryShippingController@delete');
        });
        Route::prefix('otherstate')->group(function () {
            Route::post('/', 'App\Http\Controllers\OtherStateShippingController@store');
            Route::get('/', 'App\Http\Controllers\OtherStateShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\OtherStateShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\OtherStateShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\OtherStateShippingController@delete');
        });
        Route::prefix('othercity')->group(function () {
            Route::post('/', 'App\Http\Controllers\OtherCityShippingController@store');
            Route::get('/', 'App\Http\Controllers\OtherCityShippingController@list');
            Route::get('/latest', 'App\Http\Controllers\OtherCityShippingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\OtherCityShippingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\OtherCityShippingController@delete');
        });
        Route::prefix('mastershipping')->group(function () {
            Route::post('/', 'App\Http\Controllers\MasterShippingSettingController@store');
            Route::get('/{id}', 'App\Http\Controllers\MasterShippingSettingController@retrieve');
            Route::patch('/{id}', 'App\Http\Controllers\MasterShippingSettingController@update');
            Route::delete('/{id}', 'App\Http\Controllers\MasterShippingSettingController@delete');
        });

    });

    Route::prefix('payment')->group(function() {
        Route::prefix('setting')->group(function() {
            Route::get('/', 'App\Http\Controllers\PaymentSettingController@list');
        });
    });

    Route::prefix('sms')->group(function() {
        Route::post('/sendSMSNotification', 'App\Http\Controllers\SMSController@sendLinkViaSMS');
        Route::post('/testSendSMSNotification', 'App\Http\Controllers\SMSController@testTwilioConnection');
    });
});
