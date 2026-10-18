<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosApiController;
use App\Http\Controllers\Api\PaymentGatewayController;

/*
|--------------------------------------------------------------------------
| API Routes for UangPas Lapas
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // POS Desktop Client Endpoints
    Route::prefix('pos')->group(function () {
        Route::get('/products', [PosApiController::class, 'getProducts']);
        Route::get('/inmates', [PosApiController::class, 'getInmates']);
        Route::get('/inmate/{identifier}', [PosApiController::class, 'lookupInmate']);
        Route::post('/checkout', [PosApiController::class, 'checkout']);
        Route::post('/inmate/{id}/save-fingerprint', [PosApiController::class, 'saveFingerprint']);
    });

    // Bank Payment Gateway Webhook (Virtual Account / QRIS)
    Route::post('/webhook/payment-gateway', [PaymentGatewayController::class, 'handleWebhook']);
});
