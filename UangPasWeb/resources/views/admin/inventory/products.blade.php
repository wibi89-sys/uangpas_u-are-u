@extends('layouts.admin')

@section('title', 'Katalog Barang & Inventori Retail')
@section('page_title', 'Manajemen Inventori & Katalog Barang')
@section('page_desc', 'Sistem Kontrol Stok Retail, HPP, Margin Laba, Kartu Stok & Peringatan Minimum')

@section('content')
<!-- Metric Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total SKU Barang</span>
            <div class="stat-value">{{ number_format($totalSku) }} SKU</div>
            <span style="font-size: 11px; color: var(--text-muted);">Aktif di etalase koperasi</span>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">📦</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Aset Gudang (HPP)</span>
            <div class="stat-value" style="font-size: 20px; color: #0f172a;">Rp {{ number_format($totalAssetHpp, 0, ',', '.') }}</div>
            <span style="font-size: 11px; color: #10b981;">Modal tertanam inventori</span>
        </div>
        <div class="stat-icon" style="background: #f0fdf4; color: #10b981;">💰</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Estimasi Nilai Jual</span>
            <div class="stat-value" style="font-size: 20px; color: #0284c7;">Rp {{ number_format($totalPotentialRevenue, 0, ',', '.') }}</div>
            <span style="font-size: 11px; color: #0284c7;">Potensi pendapatan kasir</span>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">🏷️</div>
    </div>

    <div class="stat-card" style="{{ $lowStockCount > 0 ? 'border-left: 4px solid #f59e0b;' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Stok Menipis / Kritis</span>
            <div class="stat-value" style="color: {{ $lowStockCount > 0 ? '#d97706' : '#10b981' }};">{{ $lowStockCount }} Item</div>
            <span style="font-size: 11px; color: var(--text-muted);">Perlu restock segera</span>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">⚠️</div>
    </div>

    <div class="stat-card" style="{{ $outOfStockCount > 0 ? 'border-left: 4px solid #ef4444;' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Stok Kosong (Habis)</span>
            <div class="stat-value" style="color: {{ $outOfStockCount > 0 ? '#dc2626' : '#10b981' }};">{{ $outOfStockCount }} Item</div>
            <span style="font-size: 11px; color: var(--text-muted);">Tidak dapat dijual</span>
        </div>
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">🚫</div>
    </div>
</div>

<!-- Filter Bar & Action Header -->
<div class="card" style="margin-bottom: 24px; padding: 18px 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <form method="GET" action="{{ route('admin.products') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex-grow: 1;">
            <div style="min-width: 200px; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Scan Barcode / Cari nama barang / rak...">
            </div>

            <div style="width: 170px;">
                <select name="kategori" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width: 180px;">
                <select name="supplier_id" onchange="this.form.submit()">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width: 160px;">
                <select name="stock_status" onchange="this.form.submit()">
                    <option value="">Status Stok</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>⚠️ Menipis</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>🚫 Habis (0)</option>
                    <option value="safe" {{ request('stock_status') == 'safe' ? 'selected' : '' }}>✅ Stok Aman</option>
                </select>
            </div>

            @if(request()->anyFilled(['search', 'kategori', 'supplier_id', 'stock_status']))
                <a href="{{ route('admin.products') }}" class="btn btn-outline" style="padding: 9px 14px;">Reset</a>
            @endif
        </form>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.inventory.restock') }}" class="btn btn-outline" style="border-color: #0284c7; color: #0284c7;">
                <span>📥</span> Form Restock
            </a>
            <button class="btn btn-primary" onclick="openModal('modalAddProduct')">
                <span>➕</span> Tambah SKU Baru
            </button>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Nama Barang & Rak</th>
                    <th>Kategori / Satuan</th>
                    <th>HPP (Beli)</th>
                    <th>Harga Jual</th>
                    <th>Margin (%)</th>
                    <th>Sisa Stok</th>
                    <th>Status</th>
                    <th>Supplier</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #1e293b; background: #f1f5f9; padding: 3px 7px; border-radius: 4px; font-size: 12px; letter-spacing: 0.5px;">
                            {{ $p->barcode }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">{{ $p->nama_barang }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                            <span>📍 {{ $p->lokasi_rak ?: 'Rak Umum' }}</span>
                            <span>•</span>
                            <span>Min: {{ $p->min_stock }} {{ $p->satuan }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="pill pill-primary" style="font-size: 11px;">{{ $p->kategori }}</span>
                        <span style="font-size: 11px; color: #64748b; margin-left: 4px;">({{ $p->satuan }})</span>
                    </td>
                    <td>
                        <span style="font-size: 13px; color: #64748b; font-weight: 600;">
                            Rp {{ number_format($p->harga_beli, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #0284c7; font-size: 13.5px;">
                            Rp {{ number_format($p->harga, 0, ',', '.') }}
                        </strong>
                    </td>
                    <td>
                        @php
                            $margin = $p->margin_percent;
                        @endphp
                        <span class="pill {{ $margin >= 20 ? 'pill-success' : ($margin >= 10 ? 'pill-primary' : 'pill-warning') }}" style="font-size: 11px;">
                            {{ $margin }}%
                        </span>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                            +Rp {{ number_format($p->margin_nominal, 0, ',', '.') }}
                        </div>
                    </td>
                    <td>
                        @if($p->stok <= 0)
                            <strong style="color: #dc2626; font-size: 14px;">0 {{ $p->satuan }}</strong>
                        @elseif($p->stok <= $p->min_stock)
                            <strong style="color: #d97706; font-size: 14px;">{{ $p->stok }} {{ $p->satuan }}</strong>
                        @else
                            <strong style="color: #10b981; font-size: 14px;">{{ $p->stok }} {{ $p->satuan }}</strong>
                        @endif
                    </td>
                    <td>
                        <span class="pill {{ $p->stock_status_badge }}">
                            {{ $p->stock_status }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #475569;">
                            {{ $p->supplier ? $p->supplier->nama_supplier : '-' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('admin.products.mutations', $p->id) }}" class="btn btn-outline" style="padding: 5px 9px; font-size: 11.5px;" title="Lihat Buku Besar Kartu Stok">
                                📜 Kartu Stok
                            </a>
                            <button class="btn btn-outline" style="padding: 5px 9px; font-size: 11.5px;" onclick="openEditModal({{ json_encode($p) }})" title="Edit Data Barang">
                                ✏️ Edit
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        Tidak ada barang yang sesuai dengan filter pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--card-border);">
        {{ $products->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Produk Retail -->
<div id="modalAddProduct" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div>
                <h3 style="font-size: 17px; font-weight: 800;">➕ Tambah SKU Barang Baru</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Lengkapi data HPP, harga jual, dan stok minimum</span>
            </div>
            <button onclick="closeModal('modalAddProduct')" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Barcode / Kode Produk *</label>
                    <input type="text" name="barcode" placeholder="Scan barcode kemasan / kode unik" required autofocus>
                </div>
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori" required>
                        <option value="Makanan & Minuman">Makanan & Minuman</option>
                        <option value="Perlengkapan Mandi">Perlengkapan Mandi</option>
                        <option value="Kebutuhan Kamar">Kebutuhan Kamar</option>
                        <option value="Rokok & Tembakau">Rokok & Tembakau</option>
                        <option value="Alat Tulis & Buku">Alat Tulis & Buku</option>
                        <option value="Lain-Lain">Lain-Lain</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Barang / Produk *</label>
                <input type="text" name="nama_barang" placeholder="Contoh: Indomie Goreng Spesial 85gr" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Harga Pokok Penjualan (HPP / Beli) *</label>
                    <input type="number" name="harga_beli" id="addHpp" placeholder="Harga beli dari supplier" min="0" step="50" required oninput="calcMargin('add')">
                </div>
                <div class="form-group">
                    <label>Harga Jual Kasir (Rp) *</label>
                    <input type="number" name="harga" id="addHarga" placeholder="Harga jual ke WBP" min="100" step="100" required oninput="calcMargin('add')">
                </div>
            </div>

            <div id="addMarginInfo" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; font-size: 12px; margin-bottom: 16px; color: #475569;">
                Margin Keuntungan: <strong id="addMarginText">-</strong>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Satuan *</label>
                    <input type="text" name="satuan" value="Pcs" placeholder="Pcs / Sachet / Pack" required>
                </div>
                <div class="form-group">
                    <label>Stok Awal (pcs) *</label>
                    <input type="number" name="stok" value="0" min="0" required>
                </div>
                <div class="form-group">
                    <label>Min. Stok Warning *</label>
                    <input type="number" name="min_stock" value="10" min="0" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Supplier Rekanan</label>
                    <select name="supplier_id">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokasi Rak / Penyimpanan</label>
                    <input type="text" name="lokasi_rak" placeholder="Contoh: Rak A-02 / Chiller 01">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddProduct')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Barang & Buat Kartu Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk Retail -->
<div id="modalEditProduct" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div>
                <h3 style="font-size: 17px; font-weight: 800;">✏️ Edit Data Barang</h3>
                <span style="font-size: 12px; color: var(--text-muted);" id="editBarcodeLabel"></span>
            </div>
            <button onclick="closeModal('modalEditProduct')" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="formEditProduct" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Barang *</label>
                <input type="text" name="nama_barang" id="editNama" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori" id="editKategori" required>
                        <option value="Makanan & Minuman">Makanan & Minuman</option>
                        <option value="Perlengkapan Mandi">Perlengkapan Mandi</option>
                        <option value="Kebutuhan Kamar">Kebutuhan Kamar</option>
                        <option value="Rokok & Tembakau">Rokok & Tembakau</option>
                        <option value="Alat Tulis & Buku">Alat Tulis & Buku</option>
                        <option value="Lain-Lain">Lain-Lain</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan *</label>
                    <input type="text" name="satuan" id="editSatuan" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Harga Pokok (HPP / Beli) *</label>
                    <input type="number" name="harga_beli" id="editHpp" min="0" step="50" required oninput="calcMargin('edit')">
                </div>
                <div class="form-group">
                    <label>Harga Jual Kasir (Rp) *</label>
                    <input type="number" name="harga" id="editHarga" min="100" step="100" required oninput="calcMargin('edit')">
                </div>
            </div>

            <div id="editMarginInfo" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; font-size: 12px; margin-bottom: 16px; color: #475569;">
                Margin Keuntungan: <strong id="editMarginText">-</strong>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Min. Stok Warning *</label>
                    <input type="number" name="min_stock" id="editMinStock" min="0" required>
                </div>
                <div class="form-group">
                    <label>Lokasi Rak / Penyimpanan</label>
                    <input type="text" name="lokasi_rak" id="editRak">
                </div>
            </div>

            <div class="form-group">
                <label>Supplier Rekanan</label>
                <select name="supplier_id" id="editSupplier">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                <input type="checkbox" name="is_active" id="editIsActive" value="1" style="width: auto;">
                <label for="editIsActive" style="margin-bottom: 0; cursor: pointer;">Tampilkan barang ini di etalase kasir POS</label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditProduct')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function calcMargin(prefix) {
        const hpp = parseFloat(document.getElementById(prefix + 'Hpp').value) || 0;
        const harga = parseFloat(document.getElementById(prefix + 'Harga').value) || 0;
        const infoEl = document.getElementById(prefix + 'MarginText');

        if (harga > 0) {
            const nominal = harga - hpp;
            const pct = ((nominal / harga) * 100).toFixed(1);
            infoEl.innerHTML = `<span style="color: ${pct >= 15 ? '#10b981' : '#f59e0b'};">Rp ${nominal.toLocaleString('id-ID')} (${pct}%)</span>`;
        } else {
            infoEl.innerText = '-';
        }
    }

    function openEditModal(prod) {
        document.getElementById('formEditProduct').action = '/products/' + prod.id;
        document.getElementById('editBarcodeLabel').innerText = 'Barcode: ' + prod.barcode;
        document.getElementById('editNama').value = prod.nama_barang;
        document.getElementById('editKategori').value = prod.kategori;
        document.getElementById('editSatuan').value = prod.satuan || 'Pcs';
        document.getElementById('editHpp').value = prod.harga_beli || 0;
        document.getElementById('editHarga').value = prod.harga;
        document.getElementById('editMinStock').value = prod.min_stock || 10;
        document.getElementById('editRak').value = prod.lokasi_rak || '';
        document.getElementById('editSupplier').value = prod.supplier_id || '';
        document.getElementById('editIsActive').checked = (prod.is_active == 1);

        calcMargin('edit');
        openModal('modalEditProduct');
    }
</script>
@endsection
