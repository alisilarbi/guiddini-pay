<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\OnlinePaymentService;
use App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate')->middleware('validate_api_keys');
});

Route::prefix('fetch')->group(function(){
    Route::post('/transaction', [PaymentController::class, 'getTransaction'])->name('get.transaction')->middleware('validate_api_keys');
});

Route::prefix('prospect')->group(function(){
    Route::post('create', [ProspectContoller::class, 'store'])->name('prospect.create')->middleware('validate_api_keys');
});