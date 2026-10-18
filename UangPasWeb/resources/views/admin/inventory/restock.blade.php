@extends('layouts.admin')

@section('title', 'Penerimaan Restock / Faktur Barang Masuk')
@section('page_title', 'Restock & Penerimaan Barang Masuk')
@section('page_desc', 'Pencatatan Faktur Supplier, Penambahan Stok Gudang & Pembaruan HPP')

@section('content')
<!-- Header Stats & Action -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Restock Bulan Ini</span>
            <div class="stat-value" style="font-size: 22px; color: #0f172a;">
                Rp {{ number_format($totalRestockBulanIni, 0, ',', '.') }}
            </div>
            <span style="font-size: 11px; color: #10b981;">Nilai faktur masuk dari supplier</span>
        </div>
        <div class="stat-icon" style="background: #f0fdf4; color: #10b981;">📥</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Faktur Dicatat</span>
            <div class="stat-value">{{ $purchases->total() }} Faktur</div>
            <span style="font-size: 11px; color: var(--text-muted);">Riwayat penerimaan barang</span>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">📑</div>
    </div>

    <div style="display: flex; align-items: center; justify-content: flex-end;">
        <button class="btn btn-primary" onclick="openModal('modalNewRestock')" style="padding: 14px 24px; font-size: 14px; width: 100%; height: 100%; justify-content: center;">
            <span style="font-size: 18px;">➕</span> Input Faktur Barang Masuk
        </button>
    </div>
</div>

<!-- Table History of Restock -->
<div class="card">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--card-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a;">Riwayat Penerimaan Faktur Supplier</h3>
        <span style="font-size: 12px; color: var(--text-muted);">Semua barang yang masuk otomatis menambah stok di kasir</span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. PO Masuk</th>
                    <th>No. Faktur Fisik</th>
                    <th>Tanggal Masuk</th>
                    <th>Supplier Rekanan</th>
                    <th>Item Diterima</th>
                    <th>Total Nominal (HPP)</th>
                    <th>Petugas Penerima</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7;">
                            {{ $p->purchase_no }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #334155; font-size: 13px;">
                            {{ $p->no_faktur_supplier ?: '-' }}
                        </strong>
                    </td>
                    <td style="font-size: 12.5px; color: #64748b;">
                        {{ $p->tanggal_masuk->format('d/m/Y') }}
                    </td>
                    <td>
                        <strong style="color: #0f172a;">
                            {{ $p->supplier ? $p->supplier->nama_supplier : '-' }}
                        </strong>
                    </td>
                    <td>
                        <div style="font-size: 12.5px; color: #334155;">
                            @foreach($p->items as $it)
                                <div>• {{ $it->product ? $it->product->nama_barang : 'Barang' }}: <strong>+{{ $it->qty }}</strong></div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 13.5px;">
                            Rp {{ number_format($p->total_nominal, 0, ',', '.') }}
                        </strong>
                    </td>
                    <td style="font-size: 12.5px; color: #475569;">
                        {{ $p->petugas_penerima }}
                    </td>
                    <td style="font-size: 12px; color: #64748b; max-width: 200px;">
                        {{ $p->catatan ?: '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        Belum ada riwayat penerimaan barang masuk. Klik tombol "Input Faktur Barang Masuk" di atas untuk menambah stok.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--card-border);">
        {{ $purchases->links() }}
    </div>
    @endif
</div>

<!-- Modal Form Input Faktur Masuk -->
<div id="modalNewRestock" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 820px; max-height: 92vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a;">📥 Form Penerimaan Barang Masuk (Restock)</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Stok gudang & kasir akan otomatis bertambah setelah disimpan</span>
            </div>
            <button onclick="closeModal('modalNewRestock')" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.inventory.restock.store') }}" method="POST" id="formRestock">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Supplier Rekanan *</label>
                    <select name="supplier_id" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Nomor Faktur Supplier</label>
                    <input type="text" name="no_faktur_supplier" placeholder="Contoh: INV-2026/X/109">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Tanggal Diterima *</label>
                    <input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Petugas Penerima Gudang *</label>
                    <input type="text" name="petugas_penerima" value="{{ session('user_role_name', 'Admin Gudang Koperasi') }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Catatan / Keterangan</label>
                    <input type="text" name="catatan" placeholder="Contoh: Pengiriman reguler mingguan">
                </div>
            </div>

            <!-- Item Rows Header -->
            <div style="border-top: 1px solid var(--card-border); padding-top: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="font-size: 14px; font-weight: 700; color: #0f172a;">Daftar Barang yang Diterima</h4>
                <button type="button" class="btn btn-outline" onclick="addRow()" style="padding: 6px 12px; font-size: 12px;">
                    ➕ Tambah Baris Barang
                </button>
            </div>

            <div id="itemRowsContainer" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <!-- Initial Row -->
                <div class="restock-row" style="display: grid; grid-template-columns: 3fr 1.2fr 1.5fr 1.8fr 40px; gap: 10px; align-items: center; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <label style="font-size: 11px; margin-bottom: 4px;">Pilih Barang</label>
                        <select name="items[0][product_id]" required onchange="onProductSelect(this, 0)">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" data-hpp="{{ $prod->harga_beli }}" data-stok="{{ $prod->stok }}" data-satuan="{{ $prod->satuan }}">
                                    {{ $prod->nama_barang }} (Sisa: {{ $prod->stok }} {{ $prod->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 11px; margin-bottom: 4px;">Qty Masuk</label>
                        <input type="number" name="items[0][qty]" min="1" value="1" required oninput="calcRow(0)">
                    </div>
                    <div>
                        <label style="font-size: 11px; margin-bottom: 4px;">Harga Beli (HPP)</label>
                        <input type="number" name="items[0][harga_beli]" id="hpp_0" min="0" step="50" required oninput="calcRow(0)">
                    </div>
                    <div>
                        <label style="font-size: 11px; margin-bottom: 4px;">Subtotal (Rp)</label>
                        <input type="text" id="subtotal_0" readonly style="background: #e2e8f0; font-weight: 700;" value="0">
                    </div>
                    <div style="padding-top: 18px;">
                        <button type="button" onclick="removeRow(this)" style="background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; font-weight: 800;">✕</button>
                    </div>
                </div>
            </div>

            <!-- Total Bar -->
            <div style="background: #f1f5f9; border-radius: 10px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <span style="font-weight: 700; color: #334155; font-size: 14px;">Total Nilai Faktur Restock:</span>
                <span style="font-size: 22px; font-weight: 800; color: #0284c7;" id="grandTotalText">Rp 0</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalNewRestock')">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 11px 22px;">Simpan Faktur & Tambahkan ke Stok</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let rowIndex = 1;
    const productsData = @json($products);

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function onProductSelect(selectEl, idx) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const hpp = opt.getAttribute('data-hpp') || 0;
        document.getElementById('hpp_' + idx).value = hpp;
        calcRow(idx);
    }

    function calcRow(idx) {
        const qtyEl = document.querySelector(`input[name="items[${idx}][qty]"]`);
        const hppEl = document.getElementById('hpp_' + idx);
        const subtotalEl = document.getElementById('subtotal_' + idx);

        if (qtyEl && hppEl && subtotalEl) {
            const qty = parseFloat(qtyEl.value) || 0;
            const hpp = parseFloat(hppEl.value) || 0;
            const subtotal = qty * hpp;
            subtotalEl.value = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        calcGrandTotal();
    }

    function calcGrandTotal() {
        let total = 0;
        const rows = document.querySelectorAll('.restock-row');
        rows.forEach((r, idx) => {
            const qtyInput = r.querySelector('input[type="number"][name*="[qty]"]');
            const hppInput = r.querySelector('input[type="number"][name*="[harga_beli]"]');
            if (qtyInput && hppInput) {
                const q = parseFloat(qtyInput.value) || 0;
                const h = parseFloat(hppInput.value) || 0;
                total += (q * h);
            }
        });
        document.getElementById('grandTotalText').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function addRow() {
        const container = document.getElementById('itemRowsContainer');
        const div = document.createElement('div');
        div.className = 'restock-row';
        div.style = 'display: grid; grid-template-columns: 3fr 1.2fr 1.5fr 1.8fr 40px; gap: 10px; align-items: center; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;';
        
        let optionsHtml = '<option value="">-- Pilih Produk --</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-hpp="${p.harga_beli}" data-stok="${p.stok}" data-satuan="${p.satuan}">${p.nama_barang} (Sisa: ${p.stok} ${p.satuan})</option>`;
        });

        div.innerHTML = `
            <div>
                <label style="font-size: 11px; margin-bottom: 4px;">Pilih Barang</label>
                <select name="items[${rowIndex}][product_id]" required onchange="onProductSelect(this, ${rowIndex})">
                    ${optionsHtml}
                </select>
            </div>
            <div>
                <label style="font-size: 11px; margin-bottom: 4px;">Qty Masuk</label>
                <input type="number" name="items[${rowIndex}][qty]" min="1" value="1" required oninput="calcRow(${rowIndex})">
            </div>
            <div>
                <label style="font-size: 11px; margin-bottom: 4px;">Harga Beli (HPP)</label>
                <input type="number" name="items[${rowIndex}][harga_beli]" id="hpp_${rowIndex}" min="0" step="50" required oninput="calcRow(${rowIndex})">
            </div>
            <div>
                <label style="font-size: 11px; margin-bottom: 4px;">Subtotal (Rp)</label>
                <input type="text" id="subtotal_${rowIndex}" readonly style="background: #e2e8f0; font-weight: 700;" value="0">
            </div>
            <div style="padding-top: 18px;">
                <button type="button" onclick="removeRow(this)" style="background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; font-weight: 800;">✕</button>
            </div>
        `;

        container.appendChild(div);
        rowIndex++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.restock-row');
        if (rows.length > 1) {
            btn.closest('.restock-row').remove();
            calcGrandTotal();
        } else {
            alert('Faktur minimal harus memiliki 1 barang.');
        }
    }
</script>
@endsection
