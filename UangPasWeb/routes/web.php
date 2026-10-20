<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\FinancialReportController;
use App\Http\Controllers\Web\ReceiptSettingController;
use App\Http\Controllers\Web\SyncController;
use App\Http\Controllers\Web\DeviceSettingController;
use App\Http\Controllers\Web\GeneralSettingController;
use App\Http\Controllers\Web\KioskController;
use App\Http\Controllers\Web\PosWebController;
use App\Http\Controllers\Web\RegisterDController;
use App\Http\Controllers\Web\EdcTerminalController;
use App\Http\Controllers\Api\PaymentGatewayController;

/*
|--------------------------------------------------------------------------
| Web Routes - Portal Manajemen UangPas Lapas
|--------------------------------------------------------------------------
*/

// Authentication Routes (Public)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Layar Kiosk Mandiri WBP (Tablet di Blok Hunian) - Publik / Standalone
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::get('/kiosk/lookup', [KioskController::class, 'lookup'])->name('kiosk.lookup');
Route::get('/kiosk/poll', [KioskController::class, 'pollDevice'])->name('kiosk.poll');

// Protected Routes (Wajib Login Petugas)
Route::middleware(['auth'])->group(function () {
    // Dashboard Utama (Semua Role yang login bisa melihat ringkasan)
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Modul Warga Binaan (Kanwil, Kalapas, Admin Koperasi, Petugas Loket)
    Route::middleware(['role:kanwil_admin,kalapas,admin_koperasi,petugas_loket'])->group(function () {
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
    });

    // Modul Buku Register D & Mini Bank WBP (Kanwil, Kalapas, Admin Koperasi, Petugas Loket, Auditor)
    Route::middleware(['role:kanwil_admin,kalapas,admin_koperasi,petugas_loket,auditor'])->group(function () {
        Route::get('/register-d', [RegisterDController::class, 'index'])->name('admin.register_d.index');
        Route::get('/register-d/{id}', [RegisterDController::class, 'show'])->name('admin.register_d.show');
        Route::get('/register-d/{id}/statement', [RegisterDController::class, 'statementPrint'])->name('admin.register_d.statement');
        Route::get('/register-d/{id}/passbook', [RegisterDController::class, 'passbookPrint'])->name('admin.register_d.passbook');
        Route::get('/register-d/reports/ledger', [RegisterDController::class, 'ledgerPrint'])->name('admin.register_d.ledger');
        Route::get('/register-d/receipt/{trxId}', [RegisterDController::class, 'receiptPrint'])->name('admin.register_d.receipt');
        Route::get('/register-d/{id}/release-receipt', [RegisterDController::class, 'releaseReceiptPrint'])->name('admin.register_d.release_receipt');
    });

    // Manajemen Petugas & Hak Akses (Kanwil Admin, Kalapas, Admin Utama UPT & Koperasi)
    Route::middleware(['role:kanwil_admin,kalapas,admin_koperasi,perm:users_manage'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle_status');
        Route::post('/users/switch-role', [UserController::class, 'switchRole'])->name('admin.users.switch_role');
    });

    // Loket Top Up & Setor Tunai (Kanwil, Admin Koperasi, Petugas Loket, Kasir)
    Route::middleware(['role:kanwil_admin,admin_koperasi,petugas_loket,kasir'])->group(function () {
        Route::get('/topup', [AdminController::class, 'topup'])->name('admin.topup');
        Route::post('/topup', [AdminController::class, 'storeTopup'])->name('admin.topup.store');
        Route::post('/topup/simulate-gateway', [PaymentGatewayController::class, 'handleWebhook'])->name('admin.topup.gateway');
    });

    // Manajemen Inventori & Retail Koperasi (Kanwil & Admin Koperasi)
    Route::middleware(['role:kanwil_admin,admin_koperasi'])->group(function () {
        Route::get('/products', [InventoryController::class, 'products'])->name('admin.products');
        Route::get('/products/print-barcodes', [InventoryController::class, 'printBarcodes'])->name('admin.products.print_barcodes');
        Route::post('/products', [InventoryController::class, 'storeProduct'])->name('admin.products.store');
        Route::put('/products/{id}', [InventoryController::class, 'updateProduct'])->name('admin.products.update');
        Route::get('/products/{id}/mutations', [InventoryController::class, 'mutations'])->name('admin.products.mutations');

        Route::get('/inventory/restock', [InventoryController::class, 'restockIndex'])->name('admin.inventory.restock');
        Route::post('/inventory/restock', [InventoryController::class, 'storeRestock'])->name('admin.inventory.restock.store');

        Route::get('/inventory/opname', [InventoryController::class, 'opnameIndex'])->name('admin.inventory.opname');
        Route::post('/inventory/opname', [InventoryController::class, 'storeOpname'])->name('admin.inventory.opname.store');
        Route::get('/inventory/opname/{id}', [InventoryController::class, 'opnameDetail'])->name('admin.inventory.opname.detail');

        Route::get('/inventory/suppliers', [InventoryController::class, 'suppliers'])->name('admin.inventory.suppliers');
        Route::post('/inventory/suppliers', [InventoryController::class, 'storeSupplier'])->name('admin.inventory.suppliers.store');
        Route::put('/inventory/suppliers/{id}', [InventoryController::class, 'updateSupplier'])->name('admin.inventory.suppliers.update');

        Route::get('/settings', [GeneralSettingController::class, 'index'])->name('admin.settings');
        Route::get('/settings/general', [GeneralSettingController::class, 'index'])->name('admin.settings.general');
        Route::post('/settings/general', [GeneralSettingController::class, 'update'])->name('admin.settings.general.update');
        Route::post('/settings/toggle-mode', [GeneralSettingController::class, 'toggleMode'])->name('admin.settings.toggle_mode');

        Route::get('/settings/receipt', [ReceiptSettingController::class, 'index'])->name('admin.settings.receipt');
        Route::post('/settings/receipt', [ReceiptSettingController::class, 'update'])->name('admin.settings.receipt.update');

        Route::get('/settings/devices', [DeviceSettingController::class, 'index'])->name('admin.settings.devices');
        Route::post('/settings/devices', [DeviceSettingController::class, 'update'])->name('admin.settings.devices.update');
        Route::post('/settings/devices/test', [DeviceSettingController::class, 'testConnection'])->name('admin.settings.devices.test');
        Route::post('/settings/devices/sync-inmates', [DeviceSettingController::class, 'syncInmates'])->name('admin.settings.devices.sync');
        Route::post('/settings/devices/pull-logs', [DeviceSettingController::class, 'pullLogs'])->name('admin.settings.devices.pull_logs');

        Route::get('/sync', [SyncController::class, 'index'])->name('admin.sync');
        Route::post('/sync/backup', [SyncController::class, 'triggerBackup'])->name('admin.sync.backup');
        Route::post('/sync/pull-mutasi', [SyncController::class, 'pullMutation'])->name('admin.sync.pull');
        Route::post('/sync/settings', [SyncController::class, 'updateSettings'])->name('admin.sync.settings');
    });

    // Laporan Keuangan, Omzet & Mutasi (Kanwil, Kalapas, Admin Koperasi, Auditor)
    Route::middleware(['role:kanwil_admin,kalapas,admin_koperasi,auditor'])->group(function () {
        Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
        Route::get('/transactions/{id}/receipt', [ReceiptSettingController::class, 'printReceipt'])->name('admin.transactions.receipt');
        Route::get('/reports/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('admin.reports.profit_loss');
        Route::get('/reports/cashflow', [FinancialReportController::class, 'cashflow'])->name('admin.reports.cashflow');
        Route::get('/reports/settlement', [FinancialReportController::class, 'settlement'])->name('admin.reports.settlement');
        Route::get('/reports/settlement/print', [FinancialReportController::class, 'printSettlement'])->name('admin.reports.settlement.print');
        Route::get('/reports/profit-loss/print', [FinancialReportController::class, 'printProfitLoss'])->name('admin.reports.print');
        Route::get('/reports/profit-loss/export-excel', [FinancialReportController::class, 'exportExcel'])->name('admin.reports.export_excel');
    });

    // Terminal Kasir Web POS Desktop & Mobile POS (Kanwil, Admin Koperasi, Kasir)
    Route::middleware(['role:kanwil_admin,admin_koperasi,kasir'])->group(function () {
        // Mode EDC Debit Cepat (Untuk kantin yang sudah punya POS sendiri)
        Route::get('/edc', [EdcTerminalController::class, 'index'])->name('admin.edc.index');
        Route::post('/edc/charge', [EdcTerminalController::class, 'charge'])->name('admin.edc.charge');
        Route::get('/edc/receipt/{id}', [EdcTerminalController::class, 'receipt'])->name('admin.edc.receipt');

        // Mode POS Toko Retail Bawaan
        Route::get('/pos', [PosWebController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosWebController::class, 'checkout'])->name('pos.checkout');
        Route::get('/mobile-pos', [InventoryController::class, 'mobilePos'])->name('mobile.pos');
        Route::post('/mobile-pos/checkout', [InventoryController::class, 'mobileCheckout'])->name('mobile.checkout');
    });
});
