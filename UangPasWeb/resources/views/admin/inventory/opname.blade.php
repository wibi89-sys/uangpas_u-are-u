@extends('layouts.admin')

@section('title', 'Stock Opname & Audit Fisik Koperasi')
@section('page_title', 'Stock Opname & Audit Inventori Fisik')
@section('page_desc', 'Pencocokan Stok Fisik vs Stok Sistem, Deteksi Kehilangan / Kerusakan, dan Berita Acara')

@section('content')
<!-- Header & Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <p style="font-size: 13px; color: var(--text-muted);">
            Lakukan audit fisik secara berkala (mingguan/bulanan) untuk memastikan akurasi barang di rak etalase dan gudang koperasi.
        </p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalNewOpname')">
        <span>📋</span> Mulai Audit Stock Opname Baru
    </button>
</div>

<!-- History of Stock Opnames -->
<div class="card" style="margin-bottom: 24px;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--card-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a;">Riwayat Berita Acara Stock Opname</h3>
        <span style="font-size: 12px; color: var(--text-muted);">Audit yang telah disahkan dan disesuaikan ke stok sistem</span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Berita Acara</th>
                    <th>Tanggal Audit</th>
                    <th>Petugas Pelaksana</th>
                    <th>Auditor / Saksi</th>
                    <th>Total Item Diaudit</th>
                    <th>Selisih Fisik (Qty)</th>
                    <th>Selisih Nilai (Rp HPP)</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opnames as $op)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7;">
                            {{ $op->opname_no }}
                        </span>
                    </td>
                    <td style="font-size: 12.5px; color: #64748b;">
                        {{ $op->tanggal_opname->format('d/m/Y') }}
                    </td>
                    <td>
                        <strong style="color: #0f172a;">{{ $op->petugas_opname }}</strong>
                    </td>
                    <td style="font-size: 12.5px; color: #475569;">
                        {{ $op->auditor_name ?: '-' }}
                    </td>
                    <td>
                        <span class="pill pill-primary">{{ $op->total_item }} SKU</span>
                    </td>
                    <td>
                        @if($op->total_selisih_qty < 0)
                            <strong style="color: #dc2626; font-size: 13.5px;">{{ $op->total_selisih_qty }} pcs (Minus)</strong>
                        @elseif($op->total_selisih_qty > 0)
                            <strong style="color: #0284c7; font-size: 13.5px;">+{{ $op->total_selisih_qty }} pcs (Plus)</strong>
                        @else
                            <strong style="color: #10b981; font-size: 13.5px;">Cocok (0)</strong>
                        @endif
                    </td>
                    <td>
                        @if($op->total_selisih_nominal < 0)
                            <strong style="color: #dc2626; font-size: 13.5px;">
                                -Rp {{ number_format(abs($op->total_selisih_nominal), 0, ',', '.') }}
                            </strong>
                        @elseif($op->total_selisih_nominal > 0)
                            <strong style="color: #0284c7; font-size: 13.5px;">
                                +Rp {{ number_format($op->total_selisih_nominal, 0, ',', '.') }}
                            </strong>
                        @else
                            <span style="color: #10b981; font-weight: 700;">Rp 0</span>
                        @endif
                    </td>
                    <td>
                        <span class="pill pill-success">Disesuaikan (Adjusted)</span>
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.inventory.opname.detail', $op->id) }}" class="btn btn-outline" style="padding: 5px 12px; font-size: 12px;" target="_blank">
                            🖨️ Berita Acara
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        Belum ada riwayat stock opname. Klik tombol "Mulai Audit Stock Opname Baru" untuk audit pertama kali.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($opnames->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--card-border);">
        {{ $opnames->links() }}
    </div>
    @endif
</div>

<!-- Modal Stock Opname Audit Sheet (Ala Indomaret) -->
<div id="modalNewOpname" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 950px; max-height: 92vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a;">📋 Lembar Kerja Audit Stock Opname (Fisik vs Sistem)</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Masukkan hasil hitungan fisik di rak. Sistem akan otomatis menghitung selisih dan kerugian.</span>
            </div>
            <button onclick="closeModal('modalNewOpname')" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.inventory.opname.store') }}" method="POST" id="formOpname">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Tanggal Pelaksanaan Audit *</label>
                    <input type="date" name="tanggal_opname" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Petugas Pelaksana Opname *</label>
                    <input type="text" name="petugas_opname" value="{{ session('user_role_name', 'Petugas Koperasi Lapas') }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Auditor / Saksi Pengesahan</label>
                    <input type="text" name="auditor_name" placeholder="Contoh: Siti Nurhaliza (Auditor Internal)">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label>Catatan Umum / Berita Acara</label>
                <input type="text" name="catatan" placeholder="Contoh: Audit rutin akhir bulan triwulan I tahun 2026">
            </div>

            <!-- Table of Products to Audit -->
            <div style="border: 1px solid var(--card-border); border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
                <div style="max-height: 420px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr style="position: sticky; top: 0; background: #f8fafc; z-index: 10;">
                                <th>No</th>
                                <th>Barcode & Nama Barang</th>
                                <th>Rak</th>
                                <th style="text-align: center;">Stok Sistem</th>
                                <th style="text-align: center; width: 120px;">Hitungan Fisik *</th>
                                <th style="text-align: center;">Selisih (+/-)</th>
                                <th style="text-align: right;">HPP Satuan</th>
                                <th style="text-align: right;">Selisih Nilai (Rp)</th>
                                <th style="width: 180px;">Keterangan Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $idx => $prod)
                            <tr>
                                <td style="font-size: 11.5px; color: #64748b;">{{ $idx + 1 }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $prod->id }}">
                                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">{{ $prod->nama_barang }}</div>
                                    <div style="font-family: monospace; font-size: 11px; color: #64748b;">{{ $prod->barcode }}</div>
                                </td>
                                <td style="font-size: 12px; color: #64748b;">
                                    {{ $prod->lokasi_rak ?: 'Rak Umum' }}
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 700; font-size: 14px; color: #334155;" id="sistem_{{ $idx }}">{{ $prod->stok }}</span>
                                    <span style="font-size: 11px; color: #64748b;">{{ $prod->satuan }}</span>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[{{ $idx }}][stok_fisik]" 
                                           id="fisik_{{ $idx }}" 
                                           value="{{ $prod->stok }}" 
                                           min="0" 
                                           required 
                                           style="text-align: center; font-weight: 700; font-size: 14px; padding: 6px;"
                                           oninput="calcOpnameRow({{ $idx }}, {{ $prod->stok }}, {{ $prod->harga_beli }})">
                                </td>
                                <td style="text-align: center;" id="selisih_badge_{{ $idx }}">
                                    <span class="pill pill-success" style="font-size: 11px;">0 (Cocok)</span>
                                </td>
                                <td style="text-align: right; font-size: 12.5px; color: #64748b;">
                                    Rp {{ number_format($prod->harga_beli, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-size: 13px;" id="selisih_nom_{{ $idx }}">
                                    Rp 0
                                </td>
                                <td>
                                    <input type="text" 
                                           name="items[{{ $idx }}][keterangan]" 
                                           placeholder="Alasan selisih..." 
                                           style="padding: 6px 10px; font-size: 12px;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Live Summary Bar -->
            <div style="background: #0f172a; color: #fff; border-radius: 12px; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <div style="font-size: 12px; color: #94a3b8;">Total SKU Dihitung:</div>
                    <div style="font-size: 18px; font-weight: 800;">{{ count($products) }} SKU</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #94a3b8;">Total Selisih Kuantitas:</div>
                    <div style="font-size: 18px; font-weight: 800;" id="sumSelisihQty">0 pcs</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #94a3b8;">Dampak Keuangan Selisih (HPP):</div>
                    <div style="font-size: 20px; font-weight: 800;" id="sumSelisihNom">Rp 0</div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalNewOpname')">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;" onclick="return confirm('Apakah Anda yakin ingin mengeksekusi penyesuaian stok ini? Stok di sistem kasir akan langsung disesuaikan dengan hitungan fisik.')">
                    ⚡ Eksekusi Penyesuaian Stok (Adjust Stock)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const totalProducts = {{ count($products) }};
    const rowData = @json($products);

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function calcOpnameRow(idx, stokSistem, hpp) {
        const fisikInput = document.getElementById('fisik_' + idx);
        const badgeEl = document.getElementById('selisih_badge_' + idx);
        const nomEl = document.getElementById('selisih_nom_' + idx);

        const fisik = parseInt(fisikInput.value) || 0;
        const selisihQty = fisik - stokSistem;
        const selisihNom = selisihQty * hpp;

        if (selisihQty === 0) {
            badgeEl.innerHTML = '<span class="pill pill-success" style="font-size: 11px;">0 (Cocok)</span>';
            nomEl.innerHTML = '<span style="color: #10b981;">Rp 0</span>';
        } else if (selisihQty < 0) {
            badgeEl.innerHTML = `<span class="pill pill-danger" style="font-size: 11px;">${selisihQty} (Kurang)</span>`;
            nomEl.innerHTML = `<span style="color: #dc2626;">-Rp ${Math.abs(selisihNom).toLocaleString('id-ID')}</span>`;
        } else {
            badgeEl.innerHTML = `<span class="pill pill-primary" style="font-size: 11px;">+${selisihQty} (Lebih)</span>`;
            nomEl.innerHTML = `<span style="color: #0284c7;">+Rp ${selisihNom.toLocaleString('id-ID')}</span>`;
        }

        calcTotalOpname();
    }

    function calcTotalOpname() {
        let totalQty = 0;
        let totalNom = 0;

        for (let i = 0; i < totalProducts; i++) {
            const fisikInput = document.getElementById('fisik_' + i);
            if (fisikInput) {
                const fisik = parseInt(fisikInput.value) || 0;
                const sistem = rowData[i].stok;
                const hpp = parseFloat(rowData[i].harga_beli) || 0;

                const selisih = fisik - sistem;
                totalQty += selisih;
                totalNom += (selisih * hpp);
            }
        }

        const qtyEl = document.getElementById('sumSelisihQty');
        const nomEl = document.getElementById('sumSelisihNom');

        if (totalQty === 0) {
            qtyEl.innerText = '0 pcs (Cocok)';
            qtyEl.style.color = '#10b981';
        } else if (totalQty < 0) {
            qtyEl.innerText = `${totalQty} pcs (Minus)`;
            qtyEl.style.color = '#ef4444';
        } else {
            qtyEl.innerText = `+${totalQty} pcs (Plus)`;
            qtyEl.style.color = '#38bdf8';
        }

        if (totalNom === 0) {
            nomEl.innerText = 'Rp 0';
            nomEl.style.color = '#10b981';
        } else if (totalNom < 0) {
            nomEl.innerText = `-Rp ${Math.abs(totalNom).toLocaleString('id-ID')}`;
            nomEl.style.color = '#ef4444';
        } else {
            nomEl.innerText = `+Rp ${totalNom.toLocaleString('id-ID')}`;
            nomEl.style.color = '#38bdf8';
        }
    }
</script>
@endsection
