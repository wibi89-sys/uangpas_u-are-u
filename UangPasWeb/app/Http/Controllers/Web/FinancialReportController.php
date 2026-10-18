<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Inmate;
use App\Models\InmateTransfer;
use App\Models\UptSetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    /**
     * Dashboard Laporan Laba Rugi Koperasi & Analisis Retail
     */
    public function profitLoss(Request $request)
    {
        $dateFilter = $this->resolveDateRange($request);
        $startDate = $dateFilter['start'];
        $endDate = $dateFilter['end'];
        $periodLabel = $dateFilter['label'];

        $cashierFilter = $request->cashier;
        $categoryFilter = $request->category;

        // Query Base untuk Transaksi Pembelian Sukses
        $trxQuery = Transaction::where('type', 'purchase')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if (!empty($cashierFilter)) {
            $trxQuery->where('cashier_name', $cashierFilter);
        }

        $trxIds = (clone $trxQuery)->pluck('id');
        $totalTrx = $trxQuery->count();
        $totalOmset = (float)$trxQuery->sum('amount');
        $avgBasketSize = $totalTrx > 0 ? ($totalOmset / $totalTrx) : 0;

        // Query Item Belanja untuk Menghitung HPP, Qty, dan Laba Kotor
        $itemQuery = TransactionItem::whereIn('transaction_id', $trxIds);
        if (!empty($categoryFilter)) {
            $itemQuery->whereHas('product', function ($q) use ($categoryFilter) {
                $q->where('kategori', $categoryFilter);
            });
        }

        $items = $itemQuery->with('product')->get();

        $totalHpp = 0;
        $totalUnits = 0;
        $categorySummary = [];
        $productRanking = [];

        foreach ($items as $item) {
            $qty = $item->qty;
            $sellingPrice = (float)$item->harga_satuan;
            $subtotal = (float)$item->subtotal;
            
            // HPP fallback ke produk jika harga_beli belum tercatat
            $costPrice = (float)$item->harga_beli;
            if ($costPrice <= 0 && $item->product) {
                $costPrice = (float)$item->product->harga_beli;
            }

            $itemHppTotal = $costPrice * $qty;
            $itemGrossProfit = $subtotal - $itemHppTotal;

            $totalHpp += $itemHppTotal;
            $totalUnits += $qty;

            // Kategori Breakdown
            $cat = ($item->product && !empty($item->product->kategori)) ? $item->product->kategori : 'Lain-lain';
            if (!isset($categorySummary[$cat])) {
                $categorySummary[$cat] = [
                    'kategori' => $cat,
                    'qty' => 0,
                    'omset' => 0,
                    'hpp' => 0,
                    'laba' => 0,
                    'margin_pct' => 0,
                ];
            }
            $categorySummary[$cat]['qty'] += $qty;
            $categorySummary[$cat]['omset'] += $subtotal;
            $categorySummary[$cat]['hpp'] += $itemHppTotal;
            $categorySummary[$cat]['laba'] += $itemGrossProfit;

            // Produk Ranking Aggregation
            $prodKey = $item->product_id ?? $item->nama_barang;
            if (!isset($productRanking[$prodKey])) {
                $productRanking[$prodKey] = [
                    'nama_barang' => $item->nama_barang,
                    'kategori' => $cat,
                    'qty' => 0,
                    'omset' => 0,
                    'hpp' => 0,
                    'laba' => 0,
                    'margin_pct' => 0,
                    'current_stock' => $item->product ? $item->product->stok : 0,
                ];
            }
            $productRanking[$prodKey]['qty'] += $qty;
            $productRanking[$prodKey]['omset'] += $subtotal;
            $productRanking[$prodKey]['hpp'] += $itemHppTotal;
            $productRanking[$prodKey]['laba'] += $itemGrossProfit;
        }

        // Hitung Margin % Kategori
        foreach ($categorySummary as &$c) {
            $c['margin_pct'] = $c['omset'] > 0 ? round(($c['laba'] / $c['omset']) * 100, 1) : 0;
        }
        unset($c);
        uasort($categorySummary, fn($a, $b) => $b['omset'] <=> $a['omset']);

        // Hitung Margin % Produk & Urutkan
        foreach ($productRanking as &$p) {
            $p['margin_pct'] = $p['omset'] > 0 ? round(($p['laba'] / $p['omset']) * 100, 1) : 0;
        }
        unset($p);

        // Top 10 Fast Moving by Qty
        $topProductsByQty = $productRanking;
        uasort($topProductsByQty, fn($a, $b) => $b['qty'] <=> $a['qty']);
        $topProductsByQty = array_slice($topProductsByQty, 0, 10);

        // Top 10 Most Profitable by Laba
        $topProductsByProfit = $productRanking;
        uasort($topProductsByProfit, fn($a, $b) => $b['laba'] <=> $a['laba']);
        $topProductsByProfit = array_slice($topProductsByProfit, 0, 10);

        // Slow Moving Products (Produk aktif yang stoknya ada tapi penjualannya 0 atau paling minim)
        $soldProductIds = array_filter(array_keys($productRanking), fn($k) => is_numeric($k));
        $slowMovingProducts = Product::where('is_active', true)
            ->where('stok', '>', 0)
            ->whereNotIn('id', $soldProductIds)
            ->orderBy('stok', 'desc')
            ->take(8)
            ->get();

        $grossProfit = $totalOmset - $totalHpp;
        $grossMarginPct = $totalOmset > 0 ? round(($grossProfit / $totalOmset) * 100, 1) : 0;

        // Daily Trend Breakdown
        $dailyTrend = Transaction::where('type', 'purchase')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as trx_date, COUNT(*) as trx_count, SUM(amount) as daily_omset')
            ->groupBy('trx_date')
            ->orderBy('trx_date', 'asc')
            ->get();

        // Daftar Kasir & Kategori untuk Filter Dropdown
        $cashiersList = Transaction::where('type', 'purchase')
            ->distinct()
            ->pluck('cashier_name')
            ->filter();

        $categoriesList = Product::distinct()
            ->pluck('kategori')
            ->filter();

        $setting = UptSetting::current();

        return view('admin.reports.profit_loss', compact(
            'startDate',
            'endDate',
            'periodLabel',
            'totalOmset',
            'totalHpp',
            'grossProfit',
            'grossMarginPct',
            'totalTrx',
            'totalUnits',
            'avgBasketSize',
            'categorySummary',
            'topProductsByQty',
            'topProductsByProfit',
            'slowMovingProducts',
            'dailyTrend',
            'cashiersList',
            'categoriesList',
            'cashierFilter',
            'categoryFilter',
            'setting'
        ));
    }

    /**
     * Laporan Rekapitulasi Kas & Titipan Saldo Non-Tunai WBP
     */
    public function cashflow(Request $request)
    {
        $dateFilter = $this->resolveDateRange($request);
        $startDate = $dateFilter['start'];
        $endDate = $dateFilter['end'];
        $periodLabel = $dateFilter['label'];

        // Saldo Titipan WBP Berjalan Saat Ini
        $totalSaldoWbpAktif = (float)Inmate::where('status', 'aktif')->sum('saldo');
        $totalWbpAktif = Inmate::where('status', 'aktif')->count();
        $totalWbpIsolasi = Inmate::where('status', 'isolasi')->count();

        // 1. Total Setoran Masuk (Top Up)
        $topupQuery = Transaction::where('type', 'topup')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        $totalTopup = (float)$topupQuery->sum('amount');
        $countTopup = $topupQuery->count();
        $topupByVa = (float)(clone $topupQuery)->where('payment_method', 'bank_va')->sum('amount');
        $topupByCounter = (float)(clone $topupQuery)->where('payment_method', 'cash_counter')->sum('amount');

        // 2. Total Belanja Koperasi (Pengurangan Saldo WBP)
        $purchaseQuery = Transaction::where('type', 'purchase')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        $totalBelanja = (float)$purchaseQuery->sum('amount');
        $countBelanja = $purchaseQuery->count();

        // 3. Total Pencairan Saldo WBP Bebas (Cash Out)
        $releaseQuery = Transaction::where('type', 'release')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        $totalRelease = (float)$releaseQuery->sum('amount');
        $countRelease = $releaseQuery->count();

        // 4. Saldo Mutasi Dibawa
        $totalSaldoMutasi = (float)InmateTransfer::whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->sum('saldo_dibawa');

        // Net Change Titipan Dana Periode Ini
        $netFlow = $totalTopup - ($totalBelanja + $totalRelease);

        $recentTrx = Transaction::with('inmate')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->latest()
            ->take(25)
            ->get();

        $setting = UptSetting::current();

        return view('admin.reports.cashflow', compact(
            'startDate',
            'endDate',
            'periodLabel',
            'totalSaldoWbpAktif',
            'totalWbpAktif',
            'totalWbpIsolasi',
            'totalTopup',
            'countTopup',
            'topupByVa',
            'topupByCounter',
            'totalBelanja',
            'countBelanja',
            'totalRelease',
            'countRelease',
            'totalSaldoMutasi',
            'netFlow',
            'recentTrx',
            'setting'
        ));
    }

    /**
     * Cetak Berita Acara / Laporan Resmi Format Kemenkumham
     */
    public function printProfitLoss(Request $request)
    {
        $dateFilter = $this->resolveDateRange($request);
        $startDate = $dateFilter['start'];
        $endDate = $dateFilter['end'];
        $periodLabel = $dateFilter['label'];

        $trxQuery = Transaction::where('type', 'purchase')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        $totalTrx = $trxQuery->count();
        $totalOmset = (float)$trxQuery->sum('amount');
        $trxIds = $trxQuery->pluck('id');

        $items = TransactionItem::whereIn('transaction_id', $trxIds)->with('product')->get();

        $totalHpp = 0;
        $totalUnits = 0;
        $categorySummary = [];

        foreach ($items as $item) {
            $qty = $item->qty;
            $subtotal = (float)$item->subtotal;
            $costPrice = (float)$item->harga_beli;
            if ($costPrice <= 0 && $item->product) {
                $costPrice = (float)$item->product->harga_beli;
            }

            $itemHpp = $costPrice * $qty;
            $itemProfit = $subtotal - $itemHpp;

            $totalHpp += $itemHpp;
            $totalUnits += $qty;

            $cat = ($item->product && !empty($item->product->kategori)) ? $item->product->kategori : 'Lain-lain';
            if (!isset($categorySummary[$cat])) {
                $categorySummary[$cat] = [
                    'kategori' => $cat,
                    'qty' => 0,
                    'omset' => 0,
                    'hpp' => 0,
                    'laba' => 0,
                    'margin_pct' => 0,
                ];
            }
            $categorySummary[$cat]['qty'] += $qty;
            $categorySummary[$cat]['omset'] += $subtotal;
            $categorySummary[$cat]['hpp'] += $itemHpp;
            $categorySummary[$cat]['laba'] += $itemProfit;
        }

        foreach ($categorySummary as &$c) {
            $c['margin_pct'] = $c['omset'] > 0 ? round(($c['laba'] / $c['omset']) * 100, 1) : 0;
        }
        unset($c);
        uasort($categorySummary, fn($a, $b) => $b['omset'] <=> $a['omset']);

        $grossProfit = $totalOmset - $totalHpp;
        $grossMarginPct = $totalOmset > 0 ? round(($grossProfit / $totalOmset) * 100, 1) : 0;
        $setting = UptSetting::current();

        return view('admin.reports.print_profit_loss', compact(
            'startDate',
            'endDate',
            'periodLabel',
            'totalOmset',
            'totalHpp',
            'grossProfit',
            'grossMarginPct',
            'totalTrx',
            'totalUnits',
            'categorySummary',
            'setting'
        ));
    }

    /**
     * Ekspor Laporan Laba Rugi ke Format Excel / CSV Terstandar
     */
    public function exportExcel(Request $request)
    {
        $dateFilter = $this->resolveDateRange($request);
        $startDate = $dateFilter['start'];
        $endDate = $dateFilter['end'];

        $setting = UptSetting::current();
        $uptName = $setting ? $setting->upt_name : 'Lapas';

        $trxQuery = Transaction::where('type', 'purchase')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        $trxIds = $trxQuery->pluck('id');
        $items = TransactionItem::whereIn('transaction_id', $trxIds)->with(['transaction.inmate', 'product'])->get();

        $filename = 'Laporan_Laba_Rugi_' . str_replace(' ', '_', $uptName) . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items, $uptName, $startDate, $endDate, $trxQuery) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Microsoft Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header Instansi
            fputcsv($file, ['KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA REPUBLIK INDONESIA']);
            fputcsv($file, ['DIREKTORAT JENDERAL PEMASYARAKATAN']);
            fputcsv($file, ['KOPERASI PENGAYOMAN - ' . strtoupper($uptName)]);
            fputcsv($file, ['LAPORAN LABA RUGI & RINCIAN PENJUALAN RETAIL KOPERASI']);
            fputcsv($file, ['Periode:', $startDate->format('d/m/Y') . ' s.d. ' . $endDate->format('d/m/Y')]);
            fputcsv($file, []);

            // Summary Finansial
            $totalSales = 0;
            $totalCost = 0;
            foreach ($items as $it) {
                $sub = (float)$it->subtotal;
                $cost = (float)$it->harga_beli;
                if ($cost <= 0 && $it->product) {
                    $cost = (float)$it->product->harga_beli;
                }
                $totalSales += $sub;
                $totalCost += ($cost * $it->qty);
            }
            $totalProfit = $totalSales - $totalCost;
            $margin = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 2) : 0;

            fputcsv($file, ['RINGKASAN EKSEKUTIF']);
            fputcsv($file, ['Total Omset Penjualan (Gross Sales):', 'Rp ' . number_format($totalSales, 0, ',', '.')]);
            fputcsv($file, ['Total HPP / Modal Pokok (COGS):', 'Rp ' . number_format($totalCost, 0, ',', '.')]);
            fputcsv($file, ['Laba Kotor Koperasi (Gross Profit):', 'Rp ' . number_format($totalProfit, 0, ',', '.')]);
            fputcsv($file, ['Margin Keuntungan (%):', $margin . '%']);
            fputcsv($file, ['Total Transaksi Kasir:', $trxQuery->count() . ' Struk']);
            fputcsv($file, []);

            // Table Headers
            fputcsv($file, [
                'No',
                'Tanggal / Waktu',
                'No. Invoice',
                'Kasir',
                'Nama WBP',
                'No. Registrasi',
                'Kategori',
                'Nama Barang',
                'Qty',
                'HPP Satuan (Rp)',
                'Harga Jual Satuan (Rp)',
                'Total Omset (Rp)',
                'Total HPP (Rp)',
                'Laba Kotor (Rp)',
                'Margin (%)'
            ]);

            $no = 1;
            foreach ($items as $it) {
                $trx = $it->transaction;
                $inmate = $trx ? $trx->inmate : null;
                $cost = (float)$it->harga_beli;
                if ($cost <= 0 && $it->product) {
                    $cost = (float)$it->product->harga_beli;
                }
                $sub = (float)$it->subtotal;
                $hppLine = $cost * $it->qty;
                $profitLine = $sub - $hppLine;
                $marginLine = $sub > 0 ? round(($profitLine / $sub) * 100, 1) : 0;

                fputcsv($file, [
                    $no++,
                    $trx ? $trx->created_at->format('d/m/Y H:i') : '-',
                    $trx ? $trx->invoice_no : '-',
                    $trx ? $trx->cashier_name : '-',
                    $inmate ? $inmate->nama : '-',
                    $inmate ? $inmate->no_registrasi : '-',
                    $it->product ? $it->product->kategori : 'Lain-lain',
                    $it->nama_barang,
                    $it->qty,
                    $cost,
                    (float)$it->harga_satuan,
                    $sub,
                    $hppLine,
                    $profitLine,
                    $marginLine . '%'
                ]);
            }

            // Total Row
            fputcsv($file, [
                '', '', '', '', '', '', '', 'TOTAL AKUMULASI',
                $items->sum('qty'),
                '', '',
                $totalSales,
                $totalCost,
                $totalProfit,
                $margin . '%'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper Resolving Date Range Filter
     */
    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period', 'this_month');
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Hari Ini (' . $now->translatedFormat('d F Y') . ')';
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $label = 'Minggu Ini (' . $start->format('d/m') . ' - ' . $end->format('d/m/Y') . ')';
                break;
            case 'last_month':
                $lastM = $now->copy()->subMonth();
                $start = $lastM->copy()->startOfMonth();
                $end = $lastM->copy()->endOfMonth();
                $label = 'Bulan Lalu (' . $start->translatedFormat('F Y') . ')';
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $label = 'Tahun Ini (' . $now->year . ')';
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = Carbon::parse($request->start_date)->startOfDay();
                    $end = Carbon::parse($request->end_date)->endOfDay();
                    $label = 'Kustom (' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ')';
                } else {
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                    $label = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                }
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $label = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label
        ];
    }
}
