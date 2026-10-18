<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\InmateTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Dashboard Overview
     */
    public function index()
    {
        $totalWbp = Inmate::where('status', 'aktif')->count();
        $totalSaldo = Inmate::where('status', 'aktif')->sum('saldo');
        
        $todayTrx = Transaction::whereDate('created_at', today())->where('status', 'success')->count();
        $todayVolume = Transaction::whereDate('created_at', today())
            ->where('status', 'success')
            ->where('type', 'purchase')
            ->sum('amount');

        $todayTopup = Transaction::whereDate('created_at', today())
            ->where('status', 'success')
            ->where('type', 'topup')
            ->sum('amount');

        $recentTransactions = Transaction::with(['inmate', 'items'])
            ->latest()
            ->take(8)
            ->get();

        $recentTransfers = InmateTransfer::with('inmate')
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::where('stok', '<=', 20)->where('is_active', true)->get();

        return view('admin.dashboard', compact(
            'totalWbp',
            'totalSaldo',
            'todayTrx',
            'todayVolume',
            'todayTopup',
            'recentTransactions',
            'recentTransfers',
            'lowStockProducts'
        ));
    }

    /**
     * Data Narapidana / WBP
     */
    public function inmates(Request $request)
    {
        $query = Inmate::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('no_registrasi', 'like', "%{$s}%")
                  ->orWhere('blok_kamar', 'like', "%{$s}%")
                  ->orWhere('va_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('blok')) {
            $query->where('blok_kamar', 'like', "%{$request->blok}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inmates = $query->latest()->paginate(15)->withQueryString();

        return view('admin.inmates', compact('inmates'));
    }

    /**
     * Detail WBP
     */
    public function inmateDetail($id)
    {
        $inmate = Inmate::with(['transactions.items', 'transfers'])->findOrFail($id);
        return view('admin.inmate_detail', compact('inmate'));
    }

    /**
     * Tambah Warga Binaan Baru
     */
    public function storeInmate(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_registrasi' => 'required|string|unique:inmates,no_registrasi',
            'nik' => 'nullable|string|max:20',
            'blok_kamar' => 'required|string',
            'lapas_asal' => 'required|string',
            'limit_harian' => 'required|numeric|min:0',
            'saldo_awal' => 'nullable|numeric|min:0',
            'kontak_keluarga' => 'nullable|string',
        ]);

        $vaNumber = '88001' . rand(100000000, 999999999);
        while (Inmate::where('va_number', $vaNumber)->exists()) {
            $vaNumber = '88001' . rand(100000000, 999999999);
        }

        $saldoAwal = (float)($request->saldo_awal ?? 0);

        DB::transaction(function () use ($request, $vaNumber, $saldoAwal) {
            $inmate = Inmate::create([
                'no_registrasi' => $request->no_registrasi,
                'nik' => $request->nik,
                'nama' => $request->nama,
                'blok_kamar' => $request->blok_kamar,
                'lapas_asal' => $request->lapas_asal,
                'status' => 'aktif',
                'saldo' => $saldoAwal,
                'va_number' => $vaNumber,
                'limit_harian' => $request->limit_harian,
                'kontak_keluarga' => $request->kontak_keluarga,
            ]);

            if ($saldoAwal > 0) {
                Transaction::create([
                    'invoice_no' => 'INIT-' . date('YmdHis') . '-' . strtoupper(Str::random(4)),
                    'inmate_id' => $inmate->id,
                    'type' => 'topup',
                    'amount' => $saldoAwal,
                    'balance_before' => 0,
                    'balance_after' => $saldoAwal,
                    'payment_method' => 'cash_counter',
                    'reference_id' => 'SETORAN-AWAL',
                    'status' => 'success',
                    'cashier_name' => 'Petugas Registrasi Lapas',
                    'notes' => 'Setoran saldo awal registrasi WBP',
                ]);
            }
        });

        return back()->with('success', 'Warga Binaan Pemasyarakatan berhasil didaftarkan. VA Pembayaran Keluarga: ' . $vaNumber);
    }

    /**
     * Mutasi WBP (Pindah Kamar / Blok / Antar Lapas)
     */
    public function transferInmate(Request $request)
    {
        $request->validate([
            'inmate_id' => 'required|exists:inmates,id',
            'lapas_tujuan' => 'required|string',
            'blok_tujuan' => 'required|string',
            'tanggal_mutasi' => 'required|date',
            'alasan' => 'nullable|string',
        ]);

        $inmate = Inmate::findOrFail($request->inmate_id);

        InmateTransfer::create([
            'inmate_id' => $inmate->id,
            'lapas_asal' => $inmate->lapas_asal,
            'lapas_tujuan' => $request->lapas_tujuan,
            'blok_asal' => $inmate->blok_kamar,
            'blok_tujuan' => $request->blok_tujuan,
            'tanggal_mutasi' => $request->tanggal_mutasi,
            'saldo_dibawa' => $inmate->saldo,
            'petugas_mutasi' => 'Admin Mutasi Lapas',
            'alasan' => $request->alasan ?? 'Pemindahan rutin / pembinaan lanjutan',
        ]);

        $inmate->lapas_asal = $request->lapas_tujuan;
        $inmate->blok_kamar = $request->blok_tujuan;
        $inmate->save();

        return back()->with('success', 'Mutasi narapidana ' . $inmate->nama . ' ke ' . $request->lapas_tujuan . ' (' . $request->blok_tujuan . ') berhasil dicatat. Saldo Rp ' . number_format($inmate->saldo, 0, ',', '.') . ' otomatis terbawa ke tempat baru.');
    }

    /**
     * Loket Top Up Saldo & Bank Gateway Simulator
     */
    public function topup(Request $request)
    {
        $inmates = Inmate::where('status', 'aktif')->orderBy('nama')->get();
        $recentTopups = Transaction::with('inmate')
            ->where('type', 'topup')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.topup', compact('inmates', 'recentTopups'));
    }

    /**
     * Setor Tunai di Loket Kunjungan Keluarga
     */
    public function storeTopup(Request $request)
    {
        $request->validate([
            'inmate_id' => 'required|exists:inmates,id',
            'amount' => 'required|numeric|min:5000',
            'penyetor' => 'required|string',
            'hubungan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $inmate = Inmate::lockForUpdate()->find($request->inmate_id);

            $before = $inmate->saldo;
            $after = $before + $request->amount;
            $inmate->saldo = $after;
            $inmate->save();

            $invoiceNo = 'TOPUP-LOKET-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
            Transaction::create([
                'invoice_no' => $invoiceNo,
                'inmate_id' => $inmate->id,
                'type' => 'topup',
                'amount' => $request->amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'payment_method' => 'cash_counter',
                'reference_id' => 'LOKET-' . date('ymd') . '-' . rand(100, 999),
                'status' => 'success',
                'cashier_name' => 'Petugas Loket Kunjungan',
                'notes' => 'Setoran tunai kunjungan dari ' . $request->penyetor . ' (' . ($request->hubungan ?? 'Keluarga') . '). ' . ($request->notes ?? ''),
            ]);
        });

        return back()->with('success', 'Setoran tunai sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' berhasil ditambahkan ke saldo WBP.');
    }

    /**
     * Katalog Produk Koperasi
     */
    public function products()
    {
        $products = Product::orderBy('kategori')->orderBy('nama_barang')->get();
        return view('admin.products', compact('products'));
    }

    /**
     * Tambah Barang Koperasi
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|unique:products,barcode',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga' => 'required|numeric|min:100',
            'stok' => 'required|integer|min:0',
        ]);

        Product::create([
            'barcode' => $request->barcode,
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'is_active' => true,
        ]);

        return back()->with('success', 'Barang baru berhasil ditambahkan ke koperasi.');
    }

    /**
     * Update Barang Koperasi
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga' => 'required|numeric|min:100',
            'stok' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update([
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Data barang ' . $product->nama_barang . ' berhasil diperbarui.');
    }

    /**
     * Laporan Mutasi & Transaksi
     */
    public function transactions(Request $request)
    {
        $query = Transaction::with(['inmate', 'items']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('inmate', function ($qi) use ($s) {
                      $qi->where('nama', 'like', "%{$s}%")
                         ->orWhere('no_registrasi', 'like', "%{$s}%");
                  });
            });
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        $totalBelanja = (clone $query)->where('type', 'purchase')->sum('amount');
        $totalTopup = (clone $query)->where('type', 'topup')->sum('amount');

        return view('admin.transactions', compact('transactions', 'totalBelanja', 'totalTopup'));
    }
}
