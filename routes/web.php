<?php

use Illuminate\Support\Facades\Route;
use App\Services\OnlinePaymentService;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('payment')->group(function () {
    Route::get('/confirm/{client_order_id}', [PaymentController::class, 'confirm'])->name('payment.confirm');
    Route::get('/failed/{client_order_id}', [PaymentController::class, 'failed'])->name('payment.failed');
});