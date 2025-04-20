<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\ClientPaymentController;


Route::get('/', function () {
    return redirect()->to('user');
});
Route::prefix('payment')->group(function () {
    Route::get('/confirm/{order_number}', [ClientPaymentController::class, 'confirm'])->name('payment.confirm');
});

Route::prefix('client/payment')->group(function () {

    Route::get('/pdf/{order_number}', [ClientPaymentController::class, 'downloadPaymentReceipt'])
        ->name('client.payment.pdf')
        ->middleware('signed');

});

Route::get('/{slug}', [ClientPaymentController::class, 'certification'])->name('certification');











// Route::prefix('payment')->group(function () {
//     Route::get('/receipt/{order_number}', [ClientPaymentController::class, 'getPaymentReceipt'])
//         ->name('payment.receipt')
//         ->middleware('signed'); // Enforces signature verification
// });
// Route::get('/private-files/{path}', function ($path) {
//     $filePath = "private/{$path}";

//     if (Storage::disk('private')->exists($filePath)) {
//         return response()->file(Storage::disk('private')->path($filePath));
//     }

//     abort(404);
// })->where('path', '.*');

// Route::get('/failed/{order_number}', [ClientPaymentController::class, 'failed'])->name('payment.failed');

