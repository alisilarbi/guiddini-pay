<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\OnlinePaymentService;
use App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
});