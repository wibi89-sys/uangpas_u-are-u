@extends('layouts.admin')

@section('title', 'Katalog Barang Koperasi')
@section('page_title', 'Manajemen Barang Koperasi Lapas')
@section('page_desc', 'Katalog Produk, Pembaruan Harga, dan Kontrol Stok Kasir')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <p style="font-size: 13px; color: var(--text-muted);">
            Setiap perubahan harga dan stok di sini akan <strong>otomatis disinkronkan ke aplikasi Desktop POS Kasir</strong> secara instan.
        </p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalAddProduct')">
        <span>➕</span> Tambah Barang Baru
    </button>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Harga Jual (Rp)</th>
                    <th>Sisa Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #475569;">
                            {{ $p->barcode }}
                        </span>
                    </td>
                    <td>
                        <strong style="font-size: 14px; color: #0f172a;">{{ $p->nama_barang }}</strong>
                    </td>
                    <td>
                        <span class="pill pill-primary">{{ $p->kategori }}</span>
                    </td>
                    <td>
                        <strong style="color: #0284c7; font-size: 14px;">Rp {{ number_format($p->harga, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        @if($p->stok <= 20)
                            <span class="pill pill-danger" style="font-size: 12px;">{{ $p->stok }} pcs (Menipis)</span>
                        @else
                            <strong style="color: #10b981;">{{ $p->stok }} pcs</strong>
                        @endif
                    </td>
                    <td>
                        @if($p->is_active)
                            <span class="pill pill-success">Aktif di Kasir</span>
                        @else
                            <span class="pill pill-warning">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;" onclick="openEditModal({{ $p->id }}, '{{ addslashes($p->nama_barang) }}', '{{ $p->kategori }}', {{ $p->harga }}, {{ $p->stok }}, {{ $p->is_active ? 1 : 0 }})">
                            ✏️ Edit Harga / Stok
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        Belum ada barang di koperasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div id="modalAddProduct" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 520px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800;">➕ Tambah Barang Koperasi</h3>
            <button onclick="closeModal('modalAddProduct')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Barcode / Kode Barang *</label>
                <input type="text" name="barcode" placeholder="Scan barcode kemasan atau masukkan kode unik" required>
            </div>

            <div class="form-group">
                <label>Nama Barang *</label>
                <input type="text" name="nama_barang" placeholder="Contoh: Kopi Kapal Api Sachet" required>
            </div>

            <div class="form-group">
                <label>Kategori *</label>
                <select name="kategori" required>
                    <option value="Makanan & Minuman">Makanan & Minuman</option>
                    <option value="Perlengkapan Mandi">Perlengkapan Mandi</option>
                    <option value="Kebutuhan Kamar">Kebutuhan Kamar</option>
                    <option value="Rokok & Tembakau">Rokok & Tembakau</option>
                    <option value="Lain-Lain">Lain-Lain</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Harga Jual (Rp) *</label>
                    <input type="number" name="harga" placeholder="Contoh: 3500" min="100" step="100" required>
                </div>
                <div class="form-group">
                    <label>Stok Awal (pcs) *</label>
                    <input type="number" name="stok" placeholder="Contoh: 100" min="0" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddProduct')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="modalEditProduct" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 520px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800;">✏️ Update Barang Koperasi</h3>
            <button onclick="closeModal('modalEditProduct')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="formEditProduct" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Barang *</label>
                <input type="text" name="nama_barang" id="editNama" required>
            </div>

            <div class="form-group">
                <label>Kategori *</label>
                <select name="kategori" id="editKategori" required>
                    <option value="Makanan & Minuman">Makanan & Minuman</option>
                    <option value="Perlengkapan Mandi">Perlengkapan Mandi</option>
                    <option value="Kebutuhan Kamar">Kebutuhan Kamar</option>
                    <option value="Rokok & Tembakau">Rokok & Tembakau</option>
                    <option value="Lain-Lain">Lain-Lain</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Harga Jual (Rp) *</label>
                    <input type="number" name="harga" id="editHarga" min="100" step="100" required>
                </div>
                <div class="form-group">
                    <label>Stok Tersedia (pcs) *</label>
                    <input type="number" name="stok" id="editStok" min="0" required>
                </div>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                <input type="checkbox" name="is_active" id="editIsActive" value="1" style="width: auto;">
                <label for="editIsActive" style="margin-bottom: 0; cursor: pointer;">Tampilkan barang ini di kasir belanja</label>
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
    function openEditModal(id, nama, kategori, harga, stok, isActive) {
        document.getElementById('formEditProduct').action = '/products/' + id;
        document.getElementById('editNama').value = nama;
        document.getElementById('editKategori').value = kategori;
        document.getElementById('editHarga').value = harga;
        document.getElementById('editStok').value = stok;
        document.getElementById('editIsActive').checked = (isActive == 1);
        openModal('modalEditProduct');
    }
</script>
@endsection
