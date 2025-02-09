<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\OnlinePaymentService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/initiate', function (OnlinePaymentService $service, Request $request) {
    return $service->execute($request);
})->middleware('validate_api_keys');
