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
        Route::get('/receipt-settings', [\App\Http\Controllers\Web\ReceiptSettingController::class, 'apiSettings']);
        Route::get('/device-settings', function() {
            $s = \App\Models\UptSetting::current();
            return response()->json([
                'success' => true,
                'solution_device_ip' => $s->solution_device_ip,
                'solution_device_port' => $s->solution_device_port,
                'solution_comkey' => $s->solution_comkey,
                'solution_device_enabled' => $s->solution_device_enabled,
                'solution_device_location' => $s->solution_device_location,
            ]);
        });
        Route::post('/checkout', [PosApiController::class, 'checkout']);
        Route::post('/inmate/{id}/save-fingerprint', [PosApiController::class, 'saveFingerprint']);
    });

    // Bank Payment Gateway Webhook (Virtual Account / QRIS)
    Route::post('/webhook/payment-gateway', [PaymentGatewayController::class, 'handleWebhook']);

    // Multi-UPT Inter-Server Synchronization Endpoints
    Route::prefix('sync')->group(function () {
        Route::get('/ping', [\App\Http\Controllers\Api\SyncApiController::class, 'ping']);
        Route::post('/push', [\App\Http\Controllers\Api\SyncApiController::class, 'receivePush']);
        Route::post('/pull-mutated-inmates', [\App\Http\Controllers\Api\SyncApiController::class, 'pullMutatedInmates']);
    });
});

