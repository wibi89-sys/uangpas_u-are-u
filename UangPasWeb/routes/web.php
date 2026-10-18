<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Api\PaymentGatewayController;

/*
|--------------------------------------------------------------------------
| Web Routes - Portal Manajemen UangPas Lapas
|--------------------------------------------------------------------------
*/

Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

// Warga Binaan
Route::get('/inmates', [AdminController::class, 'inmates'])->name('admin.inmates');
Route::get('/inmates/{id}', [AdminController::class, 'inmateDetail'])->name('admin.inmates.detail');
Route::post('/inmates', [AdminController::class, 'storeInmate'])->name('admin.inmates.store');
Route::post('/inmates/transfer', [AdminController::class, 'transferInmate'])->name('admin.inmates.transfer');

// Loket Top Up & Simulasi Bank
Route::get('/topup', [AdminController::class, 'topup'])->name('admin.topup');
Route::post('/topup', [AdminController::class, 'storeTopup'])->name('admin.topup.store');
Route::post('/topup/simulate-gateway', [PaymentGatewayController::class, 'handleWebhook'])->name('admin.topup.gateway');

// Barang Koperasi
Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('admin.products.update');

// Laporan Transaksi Kasir
Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');

// Multi-UPT Synchronization & Backup Antar-Server
use App\Http\Controllers\Web\SyncController;
Route::get('/sync', [SyncController::class, 'index'])->name('admin.sync');
Route::post('/sync/backup', [SyncController::class, 'triggerBackup'])->name('admin.sync.backup');
Route::post('/sync/pull-mutasi', [SyncController::class, 'pullMutation'])->name('admin.sync.pull');
Route::post('/sync/settings', [SyncController::class, 'updateSettings'])->name('admin.sync.settings');

// Terminal Kasir POS dialihkan sepenuhnya ke Desktop Application (UangPasDesktop.exe)
// Fitur Web difokuskan untuk Portal Admin & Manajemen Koperasi (tanpa perangkat transaksi)
Route::redirect('/pos', '/')->name('pos.index');

