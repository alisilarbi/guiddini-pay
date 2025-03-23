<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ApplicationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate')->middleware('validate_application_api_keys');
});

Route::prefix('fetch')->group(function () {
    Route::post('/transaction', [PaymentController::class, 'getTransaction'])->name('get.transaction')->middleware('validate_partner_api_keys');
});

Route::prefix('partner')->middleware('validate_partner_api_keys')->group(function () {

    // Application Routes
    Route::prefix('application')->group(function () {
        Route::post('index', [ApplicationController::class, 'index'])->name('application.index');
        Route::post('store', [ApplicationController::class, 'store'])->name('application.store');
        Route::post('show', [ApplicationController::class, 'show'])->name('application.show');
        Route::post('update', [ApplicationController::class, 'update'])->name('application.update');
        Route::post('destroy', [ApplicationController::class, 'destroy'])->name('application.destroy');
    });

    // Prospect Routes
    Route::prefix('prospect')->group(function () {
        Route::post('convert', [ProspectController::class, 'convert'])->name('prospect.convert');
        Route::post('index', [ProspectController::class, 'index'])->name('prospect.index');
        Route::post('store', [ProspectController::class, 'store'])->name('prospect.store');
        Route::post('show', [ProspectController::class, 'show'])->name('prospect.show');
        Route::post('update', [ProspectController::class, 'update'])->name('prospect.update');
        Route::post('destroy', [ProspectController::class, 'destroy'])->name('prospect.destroy');
    });

    // License Routes
    Route::prefix('license')->group(function () {
        Route::post('index', [LicenseController::class, 'index'])->name('license.index');
        Route::post('store', [LicenseController::class, 'store'])->name('license.store');
        Route::post('show', [LicenseController::class, 'show'])->name('license.show');
        Route::post('update', [LicenseController::class, 'update'])->name('license.update');
        Route::post('destroy', [LicenseController::class, 'destroy'])->name('license.destroy');
    });

    // Client Routes
    Route::prefix('client')->group(function () {
        Route::post('index', [ClientController::class, 'index'])->name('client.index');
        Route::post('store', [ClientController::class, 'store'])->name('client.store');
        Route::post('show', [ClientController::class, 'show'])->name('client.show');
        Route::post('update', [ClientController::class, 'update'])->name('client.update');
        Route::post('destroy', [ClientController::class, 'destroy'])->name('client.destroy');
    });
});

//partner apis
// Route::prefix('application')->group(function(){
//     Route::post('index', [ApplicationController::class, 'index'])->name('application.index')->middleware('validate_partner_api_keys');
//     Route::post('store', [ApplicationController::class, 'store'])->name('application.store')->middleware('validate_partner_api_keys');
//     Route::post('show', [ApplicationController::class, 'show'])->name('application.show')->middleware('validate_partner_api_keys');
//     Route::post('update', [ApplicationController::class, 'update'])->name('application.update')->middleware('validate_partner_api_keys');
//     Route::post('destroy', [ApplicationController::class, 'destroy'])->name('application.destroy')->middleware('validate_partner_api_keys');
// });

// Route::prefix('prospect')->group(function(){
//     Route::post('convert', [ProspectController::class, 'convert'])->name('prospect.convert')->middleware('validate_partner_api_keys');

//     Route::post('index', [ProspectController::class, 'index'])->name('prospect.index')->middleware('validate_partner_api_keys');
//     Route::post('store', [ProspectController::class, 'store'])->name('prospect.store')->middleware('validate_partner_api_keys');
//     Route::post('show', [ProspectController::class, 'show'])->name('prospect.show')->middleware('validate_partner_api_keys');
//     Route::post('update', [ProspectController::class, 'update'])->name('prospect.update')->middleware('validate_partner_api_keys');
//     Route::post('destroy', [ProspectController::class, 'destroy'])->name('prospect.destroy')->middleware('validate_partner_api_keys');
// });

// Route::prefix('license')->group(function(){
//     Route::post('index', [LicenseController::class, 'index'])->name('license.index')->middleware('validate_partner_api_keys');
//     Route::post('store', [LicenseController::class, 'store'])->name('license.store')->middleware('validate_partner_api_keys');
//     Route::post('show', [LicenseController::class, 'show'])->name('license.show')->middleware('validate_partner_api_keys');
//     Route::post('update', [LicenseController::class, 'update'])->name('license.update')->middleware('validate_partner_api_keys');
//     Route::post('destroy', [LicenseController::class, 'destroy'])->name('license.destroy')->middleware('validate_partner_api_keys');
// });

// Route::prefix('client')->group(function(){
//     Route::post('index', [ClientController::class, 'index'])->name('client.index')->middleware('validate_partner_api_keys');
//     Route::post('store', [ClientController::class, 'store'])->name('client.store')->middleware('validate_partner_api_keys');
//     Route::post('show', [ClientController::class, 'show'])->name('client.show')->middleware('validate_partner_api_keys');
//     Route::post('update', [ClientController::class, 'update'])->name('client.update')->middleware('validate_partner_api_keys');
//     Route::post('destroy', [ClientController::class, 'destroy'])->name('client.destroy')->middleware('validate_partner_api_keys');
// });
