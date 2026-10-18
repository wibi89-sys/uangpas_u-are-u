@extends('layouts.admin')

@section('title', 'Data Supplier Rekanan Koperasi')
@section('page_title', 'Supplier & Distributor Rekanan')
@section('page_desc', 'Kelola Mitra Pasokan Barang Koperasi, PIC, Nomor Kontak, dan Termin Pembayaran')

@section('content')
<!-- Header Stats & Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <p style="font-size: 13px; color: var(--text-muted);">
            Data distributor resmi yang menyuplai barang dagangan kebutuhan WBP di Koperasi Lapas.
        </p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalAddSupplier')">
        <span>➕</span> Tambah Supplier Rekanan
    </button>
</div>

<!-- Table Suppliers -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Kode Supplier</th>
                    <th>Nama Distributor / Perusahaan</th>
                    <th>Kontak Person (PIC)</th>
                    <th>No. Telepon / WA</th>
                    <th>Email</th>
                    <th>Termin Pembayaran</th>
                    <th>Jumlah SKU Disuplai</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 3px 8px; border-radius: 4px; font-size: 12px;">
                            {{ $s->kode_supplier }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 14px;">{{ $s->nama_supplier }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            {{ $s->alamat ?: 'Alamat belum diinput' }}
                        </div>
                    </td>
                    <td>
                        <strong style="color: #334155;">{{ $s->kontak_person ?: '-' }}</strong>
                    </td>
                    <td>
                        <span style="font-size: 13px; color: #0284c7; font-weight: 600;">
                            {{ $s->no_telp ?: '-' }}
                        </span>
                    </td>
                    <td style="font-size: 12px; color: #64748b;">
                        {{ $s->email ?: '-' }}
                    </td>
                    <td>
                        @if($s->termin_hari == 0)
                            <span class="pill pill-success" style="font-size: 11px;">Cash / COD</span>
                        @else
                            <span class="pill pill-primary" style="font-size: 11px;">Net {{ $s->termin_hari }} Hari</span>
                        @endif
                    </td>
                    <td>
                        <span class="pill pill-primary" style="font-size: 11.5px;">{{ $s->products_count }} SKU</span>
                    </td>
                    <td>
                        @if($s->is_active)
                            <span class="pill pill-success">Aktif</span>
                        @else
                            <span class="pill pill-warning">Nonaktif</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline" style="padding: 5px 12px; font-size: 12px;" onclick="openEditModal({{ json_encode($s) }})">
                            ✏️ Edit
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        Belum ada data supplier rekanan. Klik tombol "Tambah Supplier Rekanan" untuk mendaftarkan mitra pasokan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--card-border);">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Supplier -->
<div id="modalAddSupplier" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">➕ Tambah Supplier Rekanan</h3>
            <button onclick="closeModal('modalAddSupplier')" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.inventory.suppliers.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px;">
                <div class="form-group">
                    <label>Kode Supplier *</label>
                    <input type="text" name="kode_supplier" placeholder="SUP-004" required>
                </div>
                <div class="form-group">
                    <label>Nama Perusahaan / Distributor *</label>
                    <input type="text" name="nama_supplier" placeholder="Contoh: PT Sumber Pangan Makmur" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nama PIC / Kontak Person</label>
                    <input type="text" name="kontak_person" placeholder="Contoh: Budi Santoso">
                </div>
                <div class="form-group">
                    <label>No. HP / WhatsApp</label>
                    <input type="text" name="no_telp" placeholder="Contoh: 0812-3456-7890">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Email Perusahaan</label>
                    <input type="email" name="email" placeholder="order@supplier.com">
                </div>
                <div class="form-group">
                    <label>Termin Pembayaran *</label>
                    <select name="termin_hari" required>
                        <option value="0">Cash on Delivery (COD / Tunai)</option>
                        <option value="7">Tempo 7 Hari (Net 7)</option>
                        <option value="14">Tempo 14 Hari (Net 14)</option>
                        <option value="30">Tempo 30 Hari (Net 30)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat Gudang / Kantor</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap distributor..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div id="modalEditSupplier" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">✏️ Edit Data Supplier</h3>
                <span style="font-size: 12px; color: var(--text-muted);" id="editSupplierKode"></span>
            </div>
            <button onclick="closeModal('modalEditSupplier')" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="formEditSupplier" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Perusahaan / Distributor *</label>
                <input type="text" name="nama_supplier" id="editNama" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nama PIC / Kontak Person</label>
                    <input type="text" name="kontak_person" id="editPic">
                </div>
                <div class="form-group">
                    <label>No. HP / WhatsApp</label>
                    <input type="text" name="no_telp" id="editTelp">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Email Perusahaan</label>
                    <input type="email" name="email" id="editEmail">
                </div>
                <div class="form-group">
                    <label>Termin Pembayaran *</label>
                    <select name="termin_hari" id="editTermin" required>
                        <option value="0">Cash on Delivery (COD / Tunai)</option>
                        <option value="7">Tempo 7 Hari (Net 7)</option>
                        <option value="14">Tempo 14 Hari (Net 14)</option>
                        <option value="30">Tempo 30 Hari (Net 30)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat Gudang / Kantor</label>
                <textarea name="alamat" id="editAlamat" rows="2"></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="is_active" id="editActive" value="1" style="width: auto;">
                <label for="editActive" style="margin-bottom: 0; cursor: pointer;">Status Aktif Sebagai Rekanan</label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditSupplier')">Batal</button>
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

    function openEditModal(sup) {
        document.getElementById('formEditSupplier').action = '/inventory/suppliers/' + sup.id;
        document.getElementById('editSupplierKode').innerText = 'Kode: ' + sup.kode_supplier;
        document.getElementById('editNama').value = sup.nama_supplier;
        document.getElementById('editPic').value = sup.kontak_person || '';
        document.getElementById('editTelp').value = sup.no_telp || '';
        document.getElementById('editEmail').value = sup.email || '';
        document.getElementById('editTermin').value = sup.termin_hari;
        document.getElementById('editAlamat').value = sup.alamat || '';
        document.getElementById('editActive').checked = (sup.is_active == 1);
        openModal('modalEditSupplier');
    }
</script>
@endsection
