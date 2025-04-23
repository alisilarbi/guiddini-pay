<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\ClientPaymentController;
use App\Http\Controllers\Api\Partner\PartnerClientController;
use App\Http\Controllers\Api\Partner\PartnerLicenseController;
use App\Http\Controllers\Api\Partner\PartnerProspectController;
use App\Http\Controllers\Api\Partner\PartnerApplicationController;




Route::prefix('client/payment')->middleware('validate_application_api_keys')->group(function () {

    Route::post('/initiate', [ClientPaymentController::class, 'initiate']);
Route::get('/show', [ClientPaymentController::class, 'getTransaction'])->name('api.client.payment.show');
    Route::get('/receipt', [ClientPaymentController::class, 'getPaymentReceipt'])->name('api.client.payment.receipt');
    Route::post('/email', [ClientPaymentController::class, 'emailPaymentReceipt'])->name('api.client.payment.email');

});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('partner')->middleware('validate_partner_api_keys')->group(function () {
    Route::prefix('application')->group(function () {
        Route::get('index', [PartnerApplicationController::class, 'index']);
        Route::post('store', [PartnerApplicationController::class, 'store']);
        Route::get('show', [PartnerApplicationController::class, 'show']);
        Route::patch('update', [PartnerApplicationController::class, 'update']);
        Route::delete('destroy', [PartnerApplicationController::class, 'destroy']);
        Route::patch('assign-license', [PartnerApplicationController::class, 'assignLicense']);
        Route::patch('transfer-ownership', [PartnerApplicationController::class, 'transferOwnership']);
    });

    Route::prefix('prospect')->group(function () {
        Route::post('convert', [PartnerProspectController::class, 'convert']);
        Route::get('index', [PartnerProspectController::class, 'index']);
        Route::post('store', [PartnerProspectController::class, 'store']);
        Route::get('show', [PartnerProspectController::class, 'show']);
        Route::patch('update', [PartnerProspectController::class, 'update']);
        Route::delete('destroy', [PartnerProspectController::class, 'destroy']);
    });

    Route::prefix('license')->group(function () {
        Route::get('index', [PartnerLicenseController::class, 'index']);
        Route::post('store', [PartnerLicenseController::class, 'store']);
        Route::get('show', [PartnerLicenseController::class, 'show']);
        Route::patch('update', [PartnerLicenseController::class, 'update']);
        Route::delete('destroy', [PartnerLicenseController::class, 'destroy']);
        Route::patch('transfer-ownership', [PartnerLicenseController::class, 'transferOwnership']);
    });

    Route::prefix('client')->group(function () {
        Route::get('index', [PartnerClientController::class, 'index']);
        Route::post('store', [PartnerClientController::class, 'store']);
        Route::get('show', [PartnerClientController::class, 'show']);
        Route::patch('update', [PartnerClientController::class, 'update']);
        Route::delete('destroy', [PartnerClientController::class, 'destroy']);
    });
});
