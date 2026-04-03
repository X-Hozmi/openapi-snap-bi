<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| B2B Payment Gateway Routes
|--------------------------------------------------------------------------
|
| Dedicated routes for bank and third-party service integrations
| Security-focused architecture with:
| - OAuth 2.0 Authentication
| - Client Credentials Grant
| - Potential for future expansion
|
| Integration Patterns:
| - Secure token-based authentication
| - Stateless service communication
| - Designed for financial transaction endpoints
|
| References:
| - https://laravel.com/docs/master/passport#client-credentials-grant
| - https://developer.bca.co.id/id/Dokumentasi
|
*/

Route::group([
    'prefix' => 'openapi',
    'middleware' => ['throttle:openapi'],
], function () {
    // http://example.com/openapi

    require __DIR__.'/openapi/testing.php';

    Route::prefix('v1.0')->group(function () {
        // http://example.com/openapi/v1.0

        require __DIR__.'/openapi/main.php';
    });
});
