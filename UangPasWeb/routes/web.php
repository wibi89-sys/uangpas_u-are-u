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

// Warga Binaan (Full CRUD, Sanksi Register F, Pembebasan, ID Card, Import Excel)
Route::get('/inmates', [AdminController::class, 'inmates'])->name('admin.inmates');
Route::get('/inmates/template-excel', [AdminController::class, 'downloadInmateExcelTemplate'])->name('admin.inmates.template_excel');
Route::post('/inmates/import-excel', [AdminController::class, 'importInmateExcel'])->name('admin.inmates.import_excel');
Route::get('/inmates/{id}', [AdminController::class, 'inmateDetail'])->name('admin.inmates.detail');
Route::get('/inmates/{id}/card', [AdminController::class, 'inmateCard'])->name('admin.inmates.card');
Route::post('/inmates', [AdminController::class, 'storeInmate'])->name('admin.inmates.store');
Route::put('/inmates/{id}', [AdminController::class, 'updateInmate'])->name('admin.inmates.update');
Route::post('/inmates/{id}/status', [AdminController::class, 'updateStatusInmate'])->name('admin.inmates.status');
Route::post('/inmates/{id}/release', [AdminController::class, 'releaseInmate'])->name('admin.inmates.release');
Route::post('/inmates/{id}/reset-fingerprint', [AdminController::class, 'resetFingerprint'])->name('admin.inmates.reset_fingerprint');
Route::post('/inmates/transfer', [AdminController::class, 'transferInmate'])->name('admin.inmates.transfer');
Route::delete('/inmates/{id}', [AdminController::class, 'destroyInmate'])->name('admin.inmates.destroy');

// Manajemen Petugas & Hak Akses (RBAC Kanwil, Kalapas, Kasir, dsb)
use App\Http\Controllers\Web\UserController;
Route::get('/users', [UserController::class, 'index'])->name('admin.users');
Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle_status');
Route::post('/users/switch-role', [UserController::class, 'switchRole'])->name('admin.users.switch_role');

// Loket Top Up & Simulasi Bank
Route::get('/topup', [AdminController::class, 'topup'])->name('admin.topup');
Route::post('/topup', [AdminController::class, 'storeTopup'])->name('admin.topup.store');
Route::post('/topup/simulate-gateway', [PaymentGatewayController::class, 'handleWebhook'])->name('admin.topup.gateway');

// Manajemen Inventori & Retail Koperasi
use App\Http\Controllers\Web\InventoryController;
Route::get('/products', [InventoryController::class, 'products'])->name('admin.products');
Route::post('/products', [InventoryController::class, 'storeProduct'])->name('admin.products.store');
Route::put('/products/{id}', [InventoryController::class, 'updateProduct'])->name('admin.products.update');
Route::get('/products/{id}/mutations', [InventoryController::class, 'mutations'])->name('admin.products.mutations');

// Restock / Faktur Barang Masuk
Route::get('/inventory/restock', [InventoryController::class, 'restockIndex'])->name('admin.inventory.restock');
Route::post('/inventory/restock', [InventoryController::class, 'storeRestock'])->name('admin.inventory.restock.store');

// Stock Opname (Audit Fisik vs Sistem)
Route::get('/inventory/opname', [InventoryController::class, 'opnameIndex'])->name('admin.inventory.opname');
Route::post('/inventory/opname', [InventoryController::class, 'storeOpname'])->name('admin.inventory.opname.store');
Route::get('/inventory/opname/{id}', [InventoryController::class, 'opnameDetail'])->name('admin.inventory.opname.detail');

// Data Supplier / Distributor Rekanan
Route::get('/inventory/suppliers', [InventoryController::class, 'suppliers'])->name('admin.inventory.suppliers');
Route::post('/inventory/suppliers', [InventoryController::class, 'storeSupplier'])->name('admin.inventory.suppliers.store');
Route::put('/inventory/suppliers/{id}', [InventoryController::class, 'updateSupplier'])->name('admin.inventory.suppliers.update');

// Laporan Transaksi Kasir & Keuangan Retail Koperasi
use App\Http\Controllers\Web\FinancialReportController;
use App\Http\Controllers\Web\ReceiptSettingController;
Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
Route::get('/transactions/{id}/receipt', [ReceiptSettingController::class, 'printReceipt'])->name('admin.transactions.receipt');
Route::get('/reports/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('admin.reports.profit_loss');
Route::get('/reports/cashflow', [FinancialReportController::class, 'cashflow'])->name('admin.reports.cashflow');
Route::get('/reports/profit-loss/print', [FinancialReportController::class, 'printProfitLoss'])->name('admin.reports.print');
Route::get('/reports/profit-loss/export-excel', [FinancialReportController::class, 'exportExcel'])->name('admin.reports.export_excel');

// Pengaturan Struk Kasir & Thermal Printer 58mm/80mm
Route::get('/settings/receipt', [ReceiptSettingController::class, 'index'])->name('admin.settings.receipt');
Route::post('/settings/receipt', [ReceiptSettingController::class, 'update'])->name('admin.settings.receipt.update');

// Multi-UPT Synchronization & Backup Antar-Server
use App\Http\Controllers\Web\SyncController;
Route::get('/sync', [SyncController::class, 'index'])->name('admin.sync');
Route::post('/sync/backup', [SyncController::class, 'triggerBackup'])->name('admin.sync.backup');
Route::post('/sync/pull-mutasi', [SyncController::class, 'pullMutation'])->name('admin.sync.pull');
Route::post('/sync/settings', [SyncController::class, 'updateSettings'])->name('admin.sync.settings');

// Terminal Kasir Mobile & Tablet POS (Petugas Keliling Blok / Sel Kamar)
Route::get('/mobile-pos', [InventoryController::class, 'mobilePos'])->name('mobile.pos');
Route::post('/mobile-pos/checkout', [InventoryController::class, 'mobileCheckout'])->name('mobile.checkout');
Route::redirect('/pos', '/mobile-pos')->name('pos.index');

// Pengaturan Perangkat Biometrik & Hardware Integrasi (Solution X100C / ZKTeco)
use App\Http\Controllers\Web\DeviceSettingController;
Route::get('/settings/devices', [DeviceSettingController::class, 'index'])->name('admin.settings.devices');
Route::post('/settings/devices', [DeviceSettingController::class, 'update'])->name('admin.settings.devices.update');
Route::post('/settings/devices/test', [DeviceSettingController::class, 'testConnection'])->name('admin.settings.devices.test');
Route::post('/settings/devices/sync-inmates', [DeviceSettingController::class, 'syncInmates'])->name('admin.settings.devices.sync');
Route::post('/settings/devices/pull-logs', [DeviceSettingController::class, 'pullLogs'])->name('admin.settings.devices.pull_logs');

// Layar Kiosk Cek Saldo Mandiri WBP (Tablet Android / TV Gantung / Touchscreen)
use App\Http\Controllers\Web\KioskController;
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::get('/kiosk/lookup', [KioskController::class, 'lookup'])->name('kiosk.lookup');
Route::get('/kiosk/poll', [KioskController::class, 'pollDevice'])->name('kiosk.poll');

