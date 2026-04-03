<?php

use Illuminate\Support\Facades\Route;

Route::get('/check', function () {
    return response()->json([
        'success' => true,
        'message' => 'This is OpenAPI Endpoint',
    ]);
});
