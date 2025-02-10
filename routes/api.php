<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\OnlinePaymentService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/initiate', function (OnlinePaymentService $service, Request $request) {
//     return $service->execute($request);
// })->middleware('validate_api_keys');

// Route::get('/confirm', [OnlinePaymentService::class, 'confirm'])->name('confirm');

// routes/web.php
Route::post('/process-payment', [OnlinePaymentService::class, 'execute'])->name('payment.process')->middleware('validate_api_keys');
Route::get('/payment/confirm', [OnlinePaymentService::class, 'confirm'])->name('payment.confirm');
Route::get('/payment/failed', [OnlinePaymentService::class, 'failed'])->name('payment.failed');

