@extends('layouts.admin')

@section('title', 'Data Warga Binaan')
@section('page_title', 'Manajemen Warga Binaan Pemasyarakatan (WBP)')
@section('page_desc', 'Registrasi Narapidana, Status Biometrik Sidik Jari, & Mutasi Antar Lapas')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <!-- Search & Filter Form -->
    <form method="GET" action="{{ route('admin.inmates') }}" style="display: flex; gap: 10px; flex-grow: 1; max-width: 600px;">
        <input type="text" name="search" placeholder="Cari Nama, No. Registrasi, atau No. VA..." value="{{ request('search') }}" style="flex-grow: 1;">
        <select name="status" style="width: 140px;">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="mutasi" {{ request('status') == 'mutasi' ? 'selected' : '' }}>Mutasi</option>
            <option value="bebas" {{ request('status') == 'bebas' ? 'selected' : '' }}>Bebas</option>
        </select>
        <button type="submit" class="btn btn-outline">Cari</button>
    </form>

    <!-- Add WBP Button -->
    <button class="btn btn-primary" onclick="openModal('modalAddWbp')">
        <span>➕</span> Tambah WBP Baru
    </button>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Registrasi / Nama</th>
                    <th>Penempatan</th>
                    <th>Saldo Dompet</th>
                    <th>Limit Belanja Harian</th>
                    <th>Virtual Account Keluarga</th>
                    <th>Biometrik U.are.U</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inmates as $wbp)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 14px;">{{ $wbp->nama }}</strong>
                        <div style="font-size: 11.5px; color: var(--text-muted);">
                            {{ $wbp->no_registrasi }} &bull; NIK: {{ $wbp->nik ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <span class="pill pill-primary">{{ $wbp->blok_kamar }}</span>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">{{ $wbp->lapas_asal }}</div>
                    </td>
                    <td>
                        <strong style="color: #10b981; font-size: 15px;">Rp {{ number_format($wbp->saldo, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        <span style="font-size: 12.5px; font-weight: 600;">Rp {{ number_format($wbp->limit_harian, 0, ',', '.') }}</span>
                        <div style="font-size: 11px; color: var(--text-muted);">Sisa hari ini: Rp {{ number_format($wbp->remaining_daily_limit, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 13px;">
                            {{ $wbp->va_number ?? '-' }}
                        </div>
                        <div style="font-size: 10.5px; color: var(--text-muted);">Transfer Bank Keluarga</div>
                    </td>
                    <td>
                        @if(!empty($wbp->fingerprint_template))
                            <span class="pill pill-success">✓ Terdaftar</span>
                        @else
                            <span class="pill pill-warning">⚪ Belum Rekam</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('admin.inmates.detail', $wbp->id) }}" class="btn btn-outline" style="padding: 6px 10px; font-size: 11.5px;">
                                Detail
                            </a>
                            <button class="btn btn-outline" style="padding: 6px 10px; font-size: 11.5px; color: #0284c7;" onclick="openTransferModal({{ $wbp->id }}, '{{ addslashes($wbp->nama) }}', '{{ $wbp->lapas_asal }}', '{{ $wbp->blok_kamar }}')">
                                Mutasi
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        Tidak ada data warga binaan yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $inmates->links() }}
    </div>
</div>

<!-- Modal Tambah WBP -->
<div id="modalAddWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 600px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800;">➕ Pendaftaran Warga Binaan Baru</h3>
            <button onclick="closeModal('modalAddWbp')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.inmates.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nomor Registrasi Lapas *</label>
                    <input type="text" name="no_registrasi" placeholder="Contoh: WBP-2026-0089" required>
                </div>
                <div class="form-group">
                    <label>NIK (KTP) Narapidana</label>
                    <input type="text" name="nik" placeholder="16 digit NIK">
                </div>
            </div>

            <div class="form-group">
                <label>Nama Lengkap Warga Binaan *</label>
                <input type="text" name="nama" placeholder="Nama lengkap sesuai berkas penahanan" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Lapas / Rutan *</label>
                    <input type="text" name="lapas_asal" value="Lapas Kelas I" required>
                </div>
                <div class="form-group">
                    <label>Penempatan Kamar/Blok *</label>
                    <input type="text" name="blok_kamar" placeholder="Contoh: Blok B Kamar 05" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Batas Limit Belanja Harian (Rp) *</label>
                    <input type="number" name="limit_harian" value="100000" min="0" step="5000" required>
                </div>
                <div class="form-group">
                    <label>Setoran Saldo Awal (Rp)</label>
                    <input type="number" name="saldo_awal" value="0" min="0" step="1000">
                </div>
            </div>

            <div class="form-group">
                <label>Kontak Keluarga (Penyetor)</label>
                <input type="text" name="kontak_keluarga" placeholder="No. HP / Nama keluarga yang bisa dihubungi">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddWbp')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data WBP</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Mutasi WBP -->
<div id="modalTransferWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 520px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800;">🔄 Mutasi Perpindahan WBP</h3>
            <button onclick="closeModal('modalTransferWbp')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.inmates.transfer') }}" method="POST">
            @csrf
            <input type="hidden" name="inmate_id" id="trfInmateId">

            <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                <div style="font-size: 11.5px; color: var(--text-muted);">Nama Warga Binaan:</div>
                <div id="trfWbpNama" style="font-size: 15px; font-weight: 800; color: #0284c7;">-</div>
                <div id="trfCurrentLocation" style="font-size: 12px; color: #64748b; margin-top: 2px;">-</div>
            </div>

            <div class="form-group">
                <label>Lapas / Rutan Tujuan *</label>
                <input type="text" name="lapas_tujuan" id="trfLapasTujuan" placeholder="Contoh: Lapas Narkotika Kelas IIA" required>
            </div>

            <div class="form-group">
                <label>Blok / Kamar Baru *</label>
                <input type="text" name="blok_tujuan" placeholder="Contoh: Blok C Kamar 02" required>
            </div>

            <div class="form-group">
                <label>Tanggal Pemindahan *</label>
                <input type="date" name="tanggal_mutasi" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Alasan Pemindahan</label>
                <textarea name="alasan" rows="2" placeholder="Catatan surat keputusan pemindahan..."></textarea>
            </div>

            <p style="font-size: 12px; color: #10b981; background: #ecfdf5; padding: 10px; border-radius: 6px; margin-bottom: 16px;">
                💡 <strong>Otomatis Terintegrasi</strong>: Seluruh saldo dompet dan sidik jari WBP akan otomatis aktif di kasir tujuan tanpa perlu mendaftar ulang.
            </p>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalTransferWbp')">Batal</button>
                <button type="submit" class="btn btn-primary">Proses Pemindahan WBP</button>
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
    function openTransferModal(id, nama, lapas, blok) {
        document.getElementById('trfInmateId').value = id;
        document.getElementById('trfWbpNama').innerText = nama;
        document.getElementById('trfCurrentLocation').innerText = 'Posisi Saat Ini: ' + lapas + ' (' + blok + ')';
        document.getElementById('trfLapasTujuan').value = lapas;
        openModal('modalTransferWbp');
    }
</script>
@endsection
