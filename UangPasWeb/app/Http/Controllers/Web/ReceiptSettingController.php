<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UptSetting;
use App\Models\Transaction;

class ReceiptSettingController extends Controller
{
    /**
     * Halaman Pengaturan Struk Kasir & Template Thermal Printer
     */
    public function index()
    {
        $setting = UptSetting::current();
        
        // Ambil contoh 1 transaksi riil untuk pratinjau langsung jika ada
        $sampleTrx = Transaction::with(['inmate', 'items'])
            ->where('type', 'purchase')
            ->where('status', 'success')
            ->latest()
            ->first();

        return view('admin.settings.receipt', compact('setting', 'sampleTrx'));
    }

    /**
     * Simpan Perubahan Pengaturan Struk
     */
    public function update(Request $request)
    {
        $request->validate([
            'receipt_store_name' => 'required|string|max:100',
            'receipt_address' => 'nullable|string|max:255',
            'receipt_phone' => 'nullable|string|max:50',
            'receipt_footer' => 'nullable|string|max:500',
            'receipt_paper_width' => 'required|in:58mm,80mm',
        ]);

        $setting = UptSetting::current();
        $setting->update([
            'receipt_store_name' => strtoupper($request->receipt_store_name),
            'receipt_address' => $request->receipt_address,
            'receipt_phone' => $request->receipt_phone,
            'receipt_footer' => $request->receipt_footer,
            'receipt_paper_width' => $request->receipt_paper_width,
            'receipt_auto_print' => $request->has('receipt_auto_print'),
            'receipt_show_inmate_balance' => $request->has('receipt_show_inmate_balance'),
        ]);

        return back()->with('success', 'Pengaturan struk thermal kasir berhasil diperbarui.');
    }

    /**
     * Tampilan Cetak Ulang Struk Kasir Berdasarkan ID Transaksi
     */
    public function printReceipt($id)
    {
        $transaction = Transaction::with(['inmate', 'items.product'])->findOrFail($id);
        $setting = UptSetting::current();

        return view('admin.settings.print_receipt', compact('transaction', 'setting'));
    }

    /**
     * API JSON format struk untuk Desktop POS dan Mobile POS
     */
    public function apiSettings()
    {
        $setting = UptSetting::current();

        return response()->json([
            'success' => true,
            'data' => [
                'store_name' => $setting->receipt_store_name ?? 'KOPERASI PENGAYOMAN LAPAS',
                'upt_name' => $setting->upt_name ?? 'Lapas',
                'address' => $setting->receipt_address ?? '',
                'phone' => $setting->receipt_phone ?? '',
                'footer' => $setting->receipt_footer ?? "Terima Kasih Atas Kunjungan Anda\nUangPas Lapas - Zero Cash Solution",
                'paper_width' => $setting->receipt_paper_width ?? '58mm',
                'auto_print' => (bool)$setting->receipt_auto_print,
                'show_inmate_balance' => (bool)$setting->receipt_show_inmate_balance,
            ]
        ]);
    }
}
