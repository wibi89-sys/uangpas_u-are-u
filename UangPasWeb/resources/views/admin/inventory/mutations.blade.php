@extends('layouts.admin')

@section('title', 'Buku Besar Kartu Stok - ' . $product->nama_barang)
@section('page_title', 'Kartu Stok: ' . $product->nama_barang)
@section('page_desc', 'Audit Trail Riwayat Masuk, Keluar, dan Penyesuaian Saldo Barang Koperasi')

@section('content')
<!-- Header Summary Card -->
<div class="card" style="margin-bottom: 24px; padding: 22px 28px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 56px; height: 56px; background: #e0f2fe; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                📜
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-family: monospace; font-size: 13px; font-weight: 700; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; color: #334155;">
                        {{ $product->barcode }}
                    </span>
                    <span class="pill pill-primary" style="font-size: 11px;">{{ $product->kategori }}</span>
                    <span class="pill {{ $product->stock_status_badge }}">{{ $product->stock_status }}</span>
                </div>
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $product->nama_barang }}</h2>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    📍 Lokasi: <strong>{{ $product->lokasi_rak ?: 'Etalase Umum' }}</strong> • 
                    🏭 Supplier: <strong>{{ $product->supplier ? $product->supplier->nama_supplier : '-' }}</strong> •
                    Satuan: <strong>{{ $product->satuan }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 20px; background: #f8fafc; padding: 14px 20px; border-radius: 12px; border: 1px solid var(--card-border);">
            <div style="text-align: right;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Sisa Stok Fisik</div>
                <div style="font-size: 26px; font-weight: 800; color: {{ $product->stok > $product->min_stock ? '#10b981' : ($product->stok > 0 ? '#d97706' : '#dc2626') }};">
                    {{ $product->stok }} <span style="font-size: 14px; font-weight: 600;">{{ $product->satuan }}</span>
                </div>
            </div>
            <div style="height: 36px; width: 1px; background: #cbd5e1;"></div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">HPP: <strong>Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</strong></div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Jual: <strong style="color: #0284c7;">Rp {{ number_format($product->harga, 0, ',', '.') }}</strong></div>
                <div style="font-size: 11px; color: #10b981; font-weight: 700; margin-top: 2px;">Margin: {{ $product->margin_percent }}%</div>
            </div>
        </div>
    </div>
</div>

<!-- Back button & Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
    <a href="{{ route('admin.products') }}" class="btn btn-outline" style="font-size: 12.5px;">
        <span>⬅️</span> Kembali ke Katalog Barang
    </a>

    <div style="display: flex; gap: 8px;">
        <button class="btn btn-outline" onclick="window.print()" style="font-size: 12.5px;">
            <span>🖨️</span> Cetak Kartu Stok
        </button>
        <a href="{{ route('admin.inventory.restock') }}" class="btn btn-primary" style="font-size: 12.5px;">
            <span>📥</span> Input Restock Masuk
        </a>
    </div>
</div>

<!-- Mutations Table -->
<div class="card">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--card-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a;">Buku Besar Aliran Stok (Ledger)</h3>
        <span style="font-size: 12px; color: var(--text-muted);">Urutan mutasi teranyar ke terlama</span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Waktu / Tanggal</th>
                    <th>Tipe Aktivitas</th>
                    <th>No. Referensi / Dokumen</th>
                    <th style="text-align: right;">Stok Awal</th>
                    <th style="text-align: center; color: #10b981;">Masuk (+)</th>
                    <th style="text-align: center; color: #dc2626;">Keluar (-)</th>
                    <th style="text-align: right;">Stok Akhir</th>
                    <th>Petugas Pencatat</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $m)
                <tr>
                    <td style="font-size: 12px; color: #64748b; white-space: nowrap;">
                        {{ $m->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <span class="pill {{ $m->badge_class }}" style="font-size: 11px;">
                            {{ $m->type_label }}
                        </span>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 12px; font-weight: 700; color: #334155;">
                            {{ $m->reference_no ?: '-' }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 600; color: #64748b;">
                        {{ $m->stok_awal }}
                    </td>
                    <td style="text-align: center;">
                        @if($m->qty_in > 0)
                            <strong style="color: #10b981; font-size: 13.5px;">+{{ $m->qty_in }}</strong>
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($m->qty_out > 0)
                            <strong style="color: #dc2626; font-size: 13.5px;">-{{ $m->qty_out }}</strong>
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <strong style="color: #0f172a; font-size: 14px;">{{ $m->stok_akhir }}</strong>
                    </td>
                    <td style="font-size: 12.5px; color: #334155;">
                        {{ $m->petugas ?: 'Sistem Kasir' }}
                    </td>
                    <td style="font-size: 12px; color: #64748b; max-width: 250px;">
                        {{ $m->keterangan ?: '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 36px;">
                        Belum ada catatan mutasi pergerakan stok untuk barang ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($mutations->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--card-border);">
        {{ $mutations->links() }}
    </div>
    @endif
</div>
@endsection
