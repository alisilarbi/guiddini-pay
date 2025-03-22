<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ApplicationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate')->middleware('validate_application_api_keys');
});

Route::prefix('fetch')->group(function(){
    Route::post('/transaction', [PaymentController::class, 'getTransaction'])->name('get.transaction')->middleware('validate_partner_api_keys');
});

Route::prefix('application')->group(function(){
    Route::post('index', [ApplicationController::class, 'index'])->name('application.index')->middleware('validate_partner_api_keys');
    Route::post('store', [ApplicationController::class, 'store'])->name('application.store')->middleware('validate_partner_api_keys');
    Route::post('show', [ApplicationController::class, 'show'])->name('application.show')->middleware('validate_partner_api_keys');
    Route::post('update', [ApplicationController::class, 'update'])->name('application.update')->middleware('validate_partner_api_keys');
    Route::post('destroy', [ApplicationController::class, 'destroy'])->name('application.destroy')->middleware('validate_partner_api_keys');
});


Route::prefix('prospect')->group(function(){
    Route::post('index', [ProspectController::class, 'index'])->name('Prospect.index')->middleware('validate_partner_api_keys');
    Route::post('store', [ProspectController::class, 'store'])->name('Prospect.store')->middleware('validate_partner_api_keys');
    Route::post('show', [ProspectController::class, 'show'])->name('Prospect.show')->middleware('validate_partner_api_keys');
    Route::post('update', [ProspectController::class, 'update'])->name('Prospect.update')->middleware('validate_partner_api_keys');
    Route::post('destroy', [ProspectController::class, 'destroy'])->name('Prospect.destroy')->middleware('validate_partner_api_keys');
});

