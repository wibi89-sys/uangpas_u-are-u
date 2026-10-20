<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\UptSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegisterDController extends Controller
{
    /**
     * Buku Besar Register D Lapas (Daftar Seluruh Rekening Titipan WBP)
     */
    public function index(Request $request)
    {
        $setting = UptSetting::current();

        $query = Inmate::query();

        // Search Keyword
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_registrasi', 'like', "%{$q}%")
                    ->orWhere('register_d_number', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%")
                    ->orWhere('va_number', 'like', "%{$q}%")
                    ->orWhere('blok_kamar', 'like', "%{$q}%");
            });
        }

        // Filter Blok Kamar
        if ($request->filled('blok')) {
            $query->where('blok_kamar', 'like', $request->blok . '%');
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inmates = $query->withCount('transactions')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        // Statistik Buku Register D
        $totalWbpAktif = Inmate::where('status', 'aktif')->count();
        $totalSaldoTitipan = (float) Inmate::where('status', 'aktif')->sum('saldo');
        $totalWbpIsolasi = Inmate::where('status', 'isolasi')->count();

        // Arus Bulan Berjalan
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $kreditBulanIni = (float) Transaction::where('type', 'topup')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $debitBulanIni = (float) Transaction::whereIn('type', ['purchase', 'transfer_out'])
            ->where('status', 'success')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Daftar Blok Unik untuk Dropdown Filter
        $blokList = Inmate::select('blok_kamar')
            ->distinct()
            ->orderBy('blok_kamar')
            ->pluck('blok_kamar');

        return view('admin.register_d.index', compact(
            'inmates',
            'setting',
            'totalWbpAktif',
            'totalSaldoTitipan',
            'totalWbpIsolasi',
            'kreditBulanIni',
            'debitBulanIni',
            'blokList'
        ));
    }

    /**
     * Detail Buku Tabungan / Mutasi Register D Individu WBP
     */
    public function show($id, Request $request)
    {
        $inmate = Inmate::findOrFail($id);
        $setting = UptSetting::current();

        // Resolusi Rentang Tanggal
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->subMonths(3)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        // Ambil riwayat transaksi
        $transactions = Transaction::where('inmate_id', $inmate->id)
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung total kredit dan debit pada rentang tanggal
        $totalKredit = $transactions->where('type', 'topup')->sum('amount');
        $totalDebit = $transactions->whereIn('type', ['purchase', 'transfer_out'])->sum('amount');

        return view('admin.register_d.show', compact(
            'inmate',
            'setting',
            'transactions',
            'startDate',
            'endDate',
            'totalKredit',
            'totalDebit'
        ));
    }

    /**
     * Cetak Lembar Rekening Koran Resmi Register D (A4 / PDF Ready)
     */
    public function statementPrint($id, Request $request)
    {
        $inmate = Inmate::findOrFail($id);
        $setting = UptSetting::current();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        $transactions = Transaction::where('inmate_id', $inmate->id)
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $totalKredit = $transactions->where('type', 'topup')->sum('amount');
        $totalDebit = $transactions->whereIn('type', ['purchase', 'transfer_out'])->sum('amount');

        return view('admin.register_d.statement_print', compact(
            'inmate',
            'setting',
            'transactions',
            'startDate',
            'endDate',
            'totalKredit',
            'totalDebit'
        ));
    }

    /**
     * Cetak Buku Tabungan Saku WBP (Format Passbook Mini)
     */
    public function passbookPrint($id, Request $request)
    {
        $inmate = Inmate::findOrFail($id);
        $setting = UptSetting::current();

        $transactions = Transaction::where('inmate_id', $inmate->id)
            ->where('status', 'success')
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();

        return view('admin.register_d.passbook_print', compact('inmate', 'setting', 'transactions'));
    }

    /**
     * Cetak Buku Besar Rekapitulasi Register D Kolektif (Harian / Bulanan UPT)
     */
    public function ledgerPrint(Request $request)
    {
        $setting = UptSetting::current();

        $bulan = $request->filled('month') ? Carbon::parse($request->month . '-01') : now()->startOfMonth();
        $startOfMonth = $bulan->copy()->startOfMonth();
        $endOfMonth = $bulan->copy()->endOfMonth();

        $inmates = Inmate::with(['transactions' => function ($q) use ($startOfMonth, $endOfMonth) {
            $q->where('status', 'success')
              ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }])
        ->orderBy('nama')
        ->get();

        $totalSaldoSeluruh = $inmates->sum('saldo');

        return view('admin.register_d.ledger_print', compact(
            'inmates',
            'setting',
            'bulan',
            'startOfMonth',
            'endOfMonth',
            'totalSaldoSeluruh'
        ));
    }

    /**
     * Cetak Slip Kuitansi Tanda Terima Setor Titipan Loket
     */
    public function receiptPrint($trxId)
    {
        $transaction = Transaction::with('inmate')->findOrFail($trxId);
        $setting = UptSetting::current();

        return view('admin.register_d.receipt_print', compact('transaction', 'setting'));
    }

    /**
     * Cetak Berita Acara Penyerahan Sisa Uang Titipan Register D (WBP Bebas)
     */
    public function releaseReceiptPrint($id)
    {
        $inmate = Inmate::findOrFail($id);
        $setting = UptSetting::current();

        // Cari transaksi payout pembebasan
        $releaseTrx = Transaction::where('inmate_id', $inmate->id)
            ->where('type', 'transfer_out')
            ->latest()
            ->first();

        return view('admin.register_d.release_receipt_print', compact('inmate', 'setting', 'releaseTrx'));
    }
}
