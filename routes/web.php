<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Services\Payments\ReceiptService;
use App\Livewire\Public\SponteanousPayment;
use App\Http\Controllers\Web\PaymentConfirmationController;
use \App\Services\InternalPayments\ReceiptService as InternalReceiptService;


Route::get('/', function () {
    return redirect()->to('user');
});

Route::prefix('payment')->group(function () {
    Route::get('/confirm/{order_number}', [PaymentConfirmationController::class, 'confirm'])->name('payment.confirm');
});

Route::prefix('internal/payment')->group(function () {
    Route::get('/confirm/{order_number}', [PaymentConfirmationController::class, 'internalConfirm'])->name('internal.payment.confirm');
});

Route::prefix('client/payment')->group(function () {
    Route::get('/pdf/{order_number}', [ReceiptService::class, 'downloadPaymentReceipt'])
        ->name('client.payment.pdf')
        ->middleware('signed');
});

Route::prefix('internal/payment')->group(function () {
    Route::get('/pdf/{order_number}', [InternalReceiptService::class, 'downloadPaymentReceipt'])
        ->name('internal.payment.pdf')
        ->middleware('signed');
});

Route::get('pay/{slug}/{order_number?}', SponteanousPayment::class)->name('pay');