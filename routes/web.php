<?php

use Illuminate\Support\Facades\Route;
use App\Services\OnlinePaymentService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payment/confirm', [OnlinePaymentService::class, 'confirm'])->name('payment.confirm');
Route::get('/payment/failed', [OnlinePaymentService::class, 'failed'])->name('payment.failed');
