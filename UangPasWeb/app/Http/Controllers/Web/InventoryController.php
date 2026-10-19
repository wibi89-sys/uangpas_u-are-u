<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockPurchase;
use App\Models\StockPurchaseItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockMutation;
use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * 1. Katalog Barang & Stok Retail Modern
     */
    public function products(Request $request)
    {
        $query = Product::with('supplier');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_barang', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%")
                  ->orWhere('lokasi_rak', 'like', "%{$s}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out') {
                $query->where('stok', '<=', 0);
            } elseif ($request->stock_status === 'low') {
                $query->where('stok', '>', 0)->whereColumn('stok', '<=', 'min_stock');
            } elseif ($request->stock_status === 'safe') {
                $query->whereColumn('stok', '>', 'min_stock');
            }
        }

        $products = $query->orderBy('kategori')->orderBy('nama_barang')->paginate(25)->withQueryString();

        // Summary Stats
        $allProducts = Product::all();
        $totalSku = $allProducts->count();
        $totalAssetHpp = $allProducts->sum(fn($p) => $p->stok * $p->harga_beli);
        $totalPotentialRevenue = $allProducts->sum(fn($p) => $p->stok * $p->harga);
        $lowStockCount = $allProducts->filter(fn($p) => $p->stok > 0 && $p->stok <= $p->min_stock)->count();
        $outOfStockCount = $allProducts->filter(fn($p) => $p->stok <= 0)->count();

        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $categories = Product::select('kategori')->distinct()->pluck('kategori');

        return view('admin.inventory.products', compact(
            'products',
            'suppliers',
            'categories',
            'totalSku',
            'totalAssetHpp',
            'totalPotentialRevenue',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Cetak Label Stiker Barcode & Rak Barang
     */
    public function printBarcodes(Request $request)
    {
        $query = Product::where('is_active', true);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('product_ids')) {
            $ids = is_array($request->product_ids) ? $request->product_ids : explode(',', $request->product_ids);
            $query->whereIn('id', $ids);
        }

        $products = $query->orderBy('kategori')->orderBy('nama_barang')->get();

        return view('admin.inventory.print_barcodes', compact('products'));
    }

    /**
     * Tambah Barang Retail Baru
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|unique:products,barcode',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:100',
            'satuan' => 'required|string|max:30',
            'stok' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'lokasi_rak' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::create([
                'barcode' => $request->barcode,
                'nama_barang' => $request->nama_barang,
                'kategori' => $request->kategori,
                'harga_beli' => $request->harga_beli,
                'harga' => $request->harga,
                'satuan' => $request->satuan,
                'stok' => $request->stok,
                'min_stock' => $request->min_stock,
                'supplier_id' => $request->supplier_id,
                'lokasi_rak' => $request->lokasi_rak,
                'is_active' => true,
            ]);

            if ($request->stok > 0) {
                StockMutation::record(
                    $product->id,
                    'initial',
                    $request->stok,
                    0,
                    0,
                    $request->stok,
                    'INIT-NEW',
                    session('user_role_name', 'Admin Koperasi'),
                    'Saldo stok awal pendaftaran barang baru'
                );
            }
        });

        return back()->with('success', 'Barang baru "' . $request->nama_barang . '" berhasil ditambahkan.');
    }

    /**
     * Update Barang Retail
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:100',
            'satuan' => 'required|string|max:30',
            'min_stock' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'lokasi_rak' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update([
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori,
            'harga_beli' => $request->harga_beli,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
            'min_stock' => $request->min_stock,
            'supplier_id' => $request->supplier_id,
            'lokasi_rak' => $request->lokasi_rak,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Data barang ' . $product->nama_barang . ' berhasil diperbarui.');
    }

    /**
     * 2. Kartu Stok (Stock Mutations History for single product)
     */
    public function mutations($productId)
    {
        $product = Product::with('supplier')->findOrFail($productId);
        $mutations = StockMutation::where('product_id', $productId)
            ->latest('id')
            ->paginate(30);

        return view('admin.inventory.mutations', compact('product', 'mutations'));
    }

    /**
     * 3. Pembelian / Restock Masuk
     */
    public function restockIndex(Request $request)
    {
        $purchases = StockPurchase::with(['supplier', 'items.product'])
            ->latest('tanggal_masuk')
            ->paginate(15);

        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $products = Product::where('is_active', true)->orderBy('nama_barang')->get();

        $totalRestockBulanIni = StockPurchase::whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->sum('total_nominal');

        return view('admin.inventory.restock', compact('purchases', 'suppliers', 'products', 'totalRestockBulanIni'));
    }

    /**
     * Simpan Pembelian / Restock Barang Masuk
     */
    public function storeRestock(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'no_faktur_supplier' => 'nullable|string|max:100',
            'tanggal_masuk' => 'required|date',
            'petugas_penerima' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $purchaseNo = 'PO-' . date('Ym') . '-' . strtoupper(Str::random(5));
            $totalNominal = 0;

            foreach ($request->items as $it) {
                $totalNominal += ($it['qty'] * $it['harga_beli']);
            }

            $purchase = StockPurchase::create([
                'purchase_no' => $purchaseNo,
                'no_faktur_supplier' => $request->no_faktur_supplier,
                'supplier_id' => $request->supplier_id,
                'tanggal_masuk' => $request->tanggal_masuk,
                'total_nominal' => $totalNominal,
                'status' => 'completed',
                'petugas_penerima' => $request->petugas_penerima,
                'catatan' => $request->catatan,
            ]);

            foreach ($request->items as $it) {
                $subtotal = $it['qty'] * $it['harga_beli'];

                StockPurchaseItem::create([
                    'stock_purchase_id' => $purchase->id,
                    'product_id' => $it['product_id'],
                    'qty' => $it['qty'],
                    'harga_beli_satuan' => $it['harga_beli'],
                    'subtotal' => $subtotal,
                ]);

                // Update product stock and HPP
                $product = Product::lockForUpdate()->find($it['product_id']);
                $stokAwal = $product->stok;
                $stokAkhir = $stokAwal + $it['qty'];

                $product->stok = $stokAkhir;
                // Update HPP jika harga beli berubah
                $product->harga_beli = $it['harga_beli'];
                $product->save();

                // Catat ke Kartu Stok
                StockMutation::record(
                    $product->id,
                    'purchase',
                    $it['qty'],
                    0,
                    $stokAwal,
                    $stokAkhir,
                    $purchaseNo,
                    $request->petugas_penerima,
                    'Restock Faktur: ' . ($request->no_faktur_supplier ?: $purchaseNo)
                );
            }
        });

        return back()->with('success', 'Faktur barang masuk berhasil disimpan dan stok gudang otomatis bertambah.');
    }

    /**
     * 4. Stock Opname (Audit Fisik vs Sistem)
     */
    public function opnameIndex(Request $request)
    {
        $opnames = StockOpname::with('items.product')->latest('tanggal_opname')->paginate(15);
        $products = Product::where('is_active', true)->orderBy('kategori')->orderBy('nama_barang')->get();

        return view('admin.inventory.opname', compact('opnames', 'products'));
    }

    /**
     * Eksekusi & Simpan Penyesuaian Stock Opname
     */
    public function storeOpname(Request $request)
    {
        $request->validate([
            'tanggal_opname' => 'required|date',
            'petugas_opname' => 'required|string|max:100',
            'auditor_name' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.stok_fisik' => 'required|integer|min:0',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $opnameNo = 'SO-' . date('Ym') . '-' . strtoupper(Str::random(5));
            $totalItem = 0;
            $totalSelisihQty = 0;
            $totalSelisihNominal = 0;

            $opname = StockOpname::create([
                'opname_no' => $opnameNo,
                'tanggal_opname' => $request->tanggal_opname,
                'petugas_opname' => $request->petugas_opname,
                'auditor_name' => $request->auditor_name,
                'status' => 'adjusted',
                'catatan' => $request->catatan,
            ]);

            foreach ($request->items as $it) {
                $product = Product::lockForUpdate()->find($it['product_id']);
                $stokSistem = $product->stok;
                $stokFisik = (int)$it['stok_fisik'];
                $selisihQty = $stokFisik - $stokSistem;
                $hpp = $product->harga_beli;
                $selisihNominal = $selisihQty * $hpp;

                $totalItem++;
                $totalSelisihQty += $selisihQty;
                $totalSelisihNominal += $selisihNominal;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih_qty' => $selisihQty,
                    'hpp' => $hpp,
                    'selisih_nominal' => $selisihNominal,
                    'keterangan' => $it['keterangan'] ?? null,
                ]);

                // Bila ada selisih, adjust stok di master product dan catat di kartu stok
                if ($selisihQty !== 0) {
                    $product->stok = $stokFisik;
                    $product->save();

                    StockMutation::record(
                        $product->id,
                        'opname_adj',
                        $selisihQty > 0 ? $selisihQty : 0,
                        $selisihQty < 0 ? abs($selisihQty) : 0,
                        $stokSistem,
                        $stokFisik,
                        $opnameNo,
                        $request->petugas_opname,
                        'Penyesuaian Opname: ' . ($it['keterangan'] ?: ($selisihQty < 0 ? 'Selisih kurang / rusak' : 'Selisih lebih'))
                    );
                }
            }

            $opname->update([
                'total_item' => $totalItem,
                'total_selisih_qty' => $totalSelisihQty,
                'total_selisih_nominal' => $totalSelisihNominal,
            ]);
        });

        return back()->with('success', 'Stock opname berhasil dieksekusi. Stok sistem telah disesuaikan dengan hasil audit fisik.');
    }

    /**
     * Cetak Berita Acara Stock Opname
     */
    public function opnameDetail($id)
    {
        $opname = StockOpname::with('items.product')->findOrFail($id);
        return view('admin.inventory.opname_detail', compact('opname'));
    }

    /**
     * 5. Data Supplier Rekanan Koperasi
     */
    public function suppliers(Request $request)
    {
        $suppliers = Supplier::withCount('products')->orderBy('nama_supplier')->paginate(20);
        return view('admin.inventory.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'kode_supplier' => 'required|string|unique:suppliers,kode_supplier|max:50',
            'nama_supplier' => 'required|string|max:255',
            'kontak_person' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'termin_hari' => 'required|integer|min:0',
        ]);

        Supplier::create([
            'kode_supplier' => $request->kode_supplier,
            'nama_supplier' => $request->nama_supplier,
            'kontak_person' => $request->kontak_person,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'termin_hari' => $request->termin_hari,
            'is_active' => true,
        ]);

        return back()->with('success', 'Supplier rekanan ' . $request->nama_supplier . ' berhasil didaftarkan.');
    }

    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak_person' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'termin_hari' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update([
            'nama_supplier' => $request->nama_supplier,
            'kontak_person' => $request->kontak_person,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'termin_hari' => $request->termin_hari,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Data supplier ' . $supplier->nama_supplier . ' berhasil diperbarui.');
    }

    /**
     * 6. Mobile & Tablet POS Web App (Keliling Blok / Sel Kamar WBP)
     */
    public function mobilePos(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('nama_barang')->get();
        $inmates = Inmate::where('status', 'aktif')->orderBy('nama')->get();
        $categories = Product::where('is_active', true)->select('kategori')->distinct()->pluck('kategori');

        return view('pos.mobile', compact('products', 'inmates', 'categories'));
    }

    /**
     * Eksekusi Pembayaran Mobile POS Tablet
     */
    public function mobileCheckout(Request $request)
    {
        $request->validate([
            'inmate_id' => 'required|exists:inmates,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'officer_pin' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $inmate = Inmate::lockForUpdate()->find($request->inmate_id);

            if ($inmate->status !== 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status WBP sedang ' . strtoupper($inmate->status) . '. Transaksi ditolak sistem!'
                ], 422);
            }

            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $itReq) {
                $product = Product::lockForUpdate()->find($itReq['product_id']);

                if (!$product->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang ' . $product->nama_barang . ' sedang tidak aktif.'
                    ], 422);
                }

                if ($product->stok < $itReq['qty']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok "' . $product->nama_barang . '" tidak mencukupi (sisa: ' . $product->stok . ').'
                    ], 422);
                }

                $subtotal = $product->harga * $itReq['qty'];
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product' => $product,
                    'qty' => $itReq['qty'],
                    'price' => $product->harga,
                    'subtotal' => $subtotal,
                ];
            }

            if ($inmate->saldo < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo WBP tidak mencukupi. Sisa saldo: Rp ' . number_format($inmate->saldo, 0, ',', '.') . ', Total tagihan: Rp ' . number_format($totalAmount, 0, ',', '.')
                ], 422);
            }

            $remainingLimit = $inmate->remaining_daily_limit;
            if ($totalAmount > $remainingLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi melebihi limit belanja harian Lapas! Sisa kuota hari ini: Rp ' . number_format($remainingLimit, 0, ',', '.')
                ], 422);
            }

            $balanceBefore = $inmate->saldo;
            $balanceAfter = $balanceBefore - $totalAmount;
            $inmate->saldo = $balanceAfter;
            $inmate->save();

            $invoiceNo = 'MOB-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
            $cashierName = session('user_role_name', 'Petugas Keliling Blok');

            $transaction = Transaction::create([
                'invoice_no' => $invoiceNo,
                'inmate_id' => $inmate->id,
                'type' => 'purchase',
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => 'cash_counter',
                'status' => 'success',
                'cashier_name' => $cashierName,
                'notes' => 'Transaksi Mobile POS Tablet Keliling Blok',
            ]);

            foreach ($itemsData as $it) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $it['product']->id,
                    'nama_barang' => $it['product']->nama_barang,
                    'harga_satuan' => $it['price'],
                    'harga_beli' => $it['product']->harga_beli ?? 0.00,
                    'qty' => $it['qty'],
                    'subtotal' => $it['subtotal'],
                ]);

                $stokAwal = $it['product']->stok;
                $it['product']->decrement('stok', $it['qty']);
                $stokAkhir = max(0, $stokAwal - $it['qty']);

                StockMutation::record(
                    $it['product']->id,
                    'sale',
                    0,
                    $it['qty'],
                    $stokAwal,
                    $stokAkhir,
                    $invoiceNo,
                    $cashierName,
                    'Penjualan Mobile POS Keliling Blok ' . $invoiceNo
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi belanja mobile berhasil!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'invoice_no' => $invoiceNo,
                    'wbp_nama' => $inmate->nama,
                    'no_registrasi' => $inmate->no_registrasi,
                    'total_amount' => $totalAmount,
                    'balance_after' => $balanceAfter,
                    'items_count' => count($itemsData),
                    'created_at' => now()->format('d/m/Y H:i:s')
                ]
            ]);
        });
    }
}
