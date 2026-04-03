<?php

use App\Http\Controllers\OpenAPI\Auth\AuthBToBController;
use App\Http\Controllers\OpenAPI\OauthClientController;
use App\Http\Controllers\OpenAPI\PG\Invokes\TransferVA\TVAInquiryController;
use App\Http\Controllers\OpenAPI\PG\Invokes\TransferVA\TVAPaymentController;
use App\Http\Controllers\OpenAPI\PG\Invokes\Utilities\USAuthController;
use App\Http\Controllers\OpenAPI\PG\Invokes\Utilities\USServiceController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;

// Utilities Routes
Route::group([
    'prefix' => 'utilities',
], function () {
    // http://example.com/openapi/v1.0/utilities

    // To generate auth signature for login
    Route::get('/signature-auth', USAuthController::class);

    // To generate service signature for using spesific services
    Route::post('/signature-service', USServiceController::class)->middleware('client');
});

// Service user authentication for B2B interactions
Route::post('/access-token/b2b', AuthBToBController::class);

// For managing Client Credential Grant clients
Route::apiResource('/clients', OauthClientController::class)
    ->except(['show'])
    ->middleware('client');

// Transfer Virtual Account (VA) Routes
Route::group([
    'middleware' => EnsureClientIsResourceOwner::using(['b2b:inquiry', 'b2b:payment']),
    'prefix' => 'transfer-va',
    'description' => 'Virtual Account transfer-related endpoints',
], function () {
    // http://example.com/openapi/v1.0/transfer-va
    Route::post('/inquiry', TVAInquiryController::class);
    Route::post('/payment', TVAPaymentController::class);
    // Route::post('/status', TVAStatusController::class);
});
