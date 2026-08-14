<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/services', [App\Http\Controllers\Api\ApiController::class, 'services'])->middleware('api.auth');
    Route::get('/services/{slug}', [App\Http\Controllers\Api\ApiController::class, 'service'])->middleware('api.auth');
    Route::post('/orders/lookup', [App\Http\Controllers\Api\ApiController::class, 'lookup'])->middleware('api.auth');
});