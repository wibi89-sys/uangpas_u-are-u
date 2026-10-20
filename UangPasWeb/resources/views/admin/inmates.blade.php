@extends('layouts.admin')

@section('title', 'Data Warga Binaan')
@section('page_title', 'Manajemen Warga Binaan Pemasyarakatan (WBP)')
@section('page_desc', 'Registrasi Narapidana, Status Siklus (Aktif / Register F / Bebas / Mutasi), & ID Card')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <!-- Search & Filter Form -->
    <form method="GET" action="{{ route('admin.inmates') }}" style="display: flex; gap: 10px; flex-grow: 1; max-width: 680px; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Cari Nama, No. Registrasi, atau No. VA..." value="{{ request('search') }}" style="flex-grow: 1; min-width: 220px;">
        <select name="status" style="width: 170px;">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>● Aktif</option>
            <option value="isolasi" {{ request('status') == 'isolasi' ? 'selected' : '' }}>⛔ Isolasi (Register F)</option>
            <option value="mutasi" {{ request('status') == 'mutasi' ? 'selected' : '' }}>✈️ Mutasi Antar-Lapas</option>
            <option value="bebas" {{ request('status') == 'bebas' ? 'selected' : '' }}>🕊️ Bebas</option>
        </select>
        <button type="submit" class="btn btn-outline">Cari</button>
    </form>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.inmates.template_excel') }}" class="btn btn-outline" style="border-color: #10b981; color: #047857;" title="Unduh Format Microsoft Excel (.xlsx)">
            <span>📊</span> Unduh Template Excel (.xlsx)
        </a>
        <button type="button" class="btn btn-outline" style="border-color: #0284c7; color: #0284c7;" onclick="openModal('modalImportExcel')">
            <span>📥</span> Import Excel WBP
        </button>
        <button class="btn btn-primary" onclick="openModal('modalAddWbp')">
            <span>➕</span> Registrasi WBP Baru
        </button>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Registrasi / Nama</th>
                    <th>Penempatan & Status</th>
                    <th>Saldo Dompet</th>
                    <th>Limit Belanja Harian</th>
                    <th>VA Transfer Bank</th>
                    <th>Biometrik</th>
                    <th style="min-width: 280px;">Aksi & Pengaturan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inmates as $wbp)
                <tr style="{{ $wbp->status === 'isolasi' ? 'background: rgba(254, 242, 242, 0.4);' : ($wbp->status === 'bebas' ? 'opacity: 0.75;' : '') }}">
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div>
                                <strong style="color: #0f172a; font-size: 14px;">{{ $wbp->nama }}</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted);">
                                    <span style="font-family: monospace; font-weight: 700; color: #0284c7;">{{ $wbp->no_registrasi }}</span>
                                    &bull; Reg D: <strong style="color: #0369a1;">{{ $wbp->register_d_number ?? '-' }}</strong>
                                    &bull; NIK: {{ $wbp->nik ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="pill pill-primary">{{ $wbp->blok_kamar }}</span>
                        <div style="margin-top: 4px;">
                            @if($wbp->status === 'aktif')
                                <span class="pill pill-success">● Aktif</span>
                            @elseif($wbp->status === 'isolasi')
                                <span class="pill pill-danger" title="{{ $wbp->alasan_status }}">⛔ Isolasi (Reg. F)</span>
                            @elseif($wbp->status === 'mutasi')
                                <span class="pill pill-primary">✈️ Mutasi</span>
                            @elseif($wbp->status === 'bebas')
                                <span class="pill" style="background:#e2e8f0; color:#475569;">🕊️ Bebas</span>
                            @endif
                        </div>
                        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">{{ $wbp->lapas_asal }}</div>
                    </td>
                    <td>
                        <strong style="color: #10b981; font-size: 15px;">Rp {{ number_format($wbp->saldo, 0, ',', '.') }}</strong>
                        @if($wbp->status === 'bebas' && $wbp->payout_amount > 0)
                            <div style="font-size: 10.5px; color: #0369a1;">Dicairkan: Rp {{ number_format($wbp->payout_amount, 0, ',', '.') }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 12.5px; font-weight: 600;">Rp {{ number_format($wbp->limit_harian, 0, ',', '.') }}</span>
                        <div style="font-size: 11px; color: var(--text-muted);">Sisa hari ini: Rp {{ number_format($wbp->remaining_daily_limit, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 13px;">
                            {{ $wbp->va_number ?? '-' }}
                        </div>
                        <div style="font-size: 10.5px; color: var(--text-muted);">Transfer Keluarga</div>
                    </td>
                    <td>
                        @if(!empty($wbp->fingerprint_template))
                            <span class="pill pill-success" title="Sidik Jari Siap di POS">✓ Terdaftar</span>
                        @else
                            <span class="pill pill-warning" title="Belum rekam sidik jari di POS">⚪ Belum Rekam</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <a href="{{ route('admin.register_d.show', $wbp->id) }}" class="btn btn-outline" style="padding: 5px 8px; font-size: 11px; color: #0284c7; font-weight: 700;" title="Buka Buku Register D">
                                📖 Reg D
                            </a>
                            <a href="{{ route('admin.inmates.detail', $wbp->id) }}" class="btn btn-outline" style="padding: 5px 8px; font-size: 11px;">
                                Detail
                            </a>
                            <button class="btn btn-outline" style="padding: 5px 8px; font-size: 11px;" onclick="openEditModal({{ json_encode($wbp) }})">
                                ✏️ Edit
                            </button>
                            <a href="{{ route('admin.inmates.card', $wbp->id) }}" target="_blank" class="btn btn-outline" style="padding: 5px 8px; font-size: 11px; color: #0284c7;" title="Cetak ID Card Barcode">
                                🪪 Kartu
                            </a>

                            @if($wbp->status !== 'bebas')
                                <button class="btn btn-outline" style="padding: 5px 8px; font-size: 11px; color: {{ $wbp->status === 'isolasi' ? '#10b981' : '#ef4444' }};" onclick="openStatusModal({{ $wbp->id }}, '{{ addslashes($wbp->nama) }}', '{{ $wbp->status }}', '{{ addslashes($wbp->alasan_status ?? '') }}')">
                                    {{ $wbp->status === 'isolasi' ? 'Buka Isolasi' : '⛔ Sanksi' }}
                                </button>
                                <button class="btn btn-outline" style="padding: 5px 8px; font-size: 11px; color: #059669;" onclick="openReleaseModal({{ $wbp->id }}, '{{ addslashes($wbp->nama) }}', {{ $wbp->saldo }})">
                                    🕊️ Bebaskan
                                </button>
                                <button class="btn btn-outline" style="padding: 5px 8px; font-size: 11px; color: #6366f1;" onclick="openTransferModal({{ $wbp->id }}, '{{ addslashes($wbp->nama) }}', '{{ $wbp->lapas_asal }}', '{{ $wbp->blok_kamar }}')">
                                    ✈️ Mutasi
                                </button>
                            @endif

                            @if(!empty($wbp->fingerprint_template))
                                <form action="{{ route('admin.inmates.reset_fingerprint', $wbp->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reset sidik jari WBP ini agar dapat direkam ulang?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline" style="padding: 5px 8px; font-size: 11px; color: #f59e0b;" title="Reset Biometrik">
                                        🔄 Reset Jari
                                    </button>
                                </form>
                            @endif
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

<!-- ========================================== -->
<!-- MODAL 1: Tambah WBP Baru                    -->
<!-- ========================================== -->
<div id="modalAddWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">➕ Registrasi Warga Binaan Baru</h3>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nomor Buku Register D (Opsional)</label>
                    <input type="text" name="register_d_number" placeholder="Otomatis jika dikosongkan">
                </div>
                <div class="form-group">
                    <label>Nama Penjamin / Keluarga Terdekat</label>
                    <input type="text" name="penjamin_nama" placeholder="Nama keluarga penjamin">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Hubungan Penjamin</label>
                    <input type="text" name="penjamin_hubungan" placeholder="Istri, Ibu, Anak...">
                </div>
                <div class="form-group">
                    <label>No. HP / WA Penjamin</label>
                    <input type="text" name="penjamin_telepon" placeholder="Contoh: 08123456789">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddWbp')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data WBP</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: Edit Data WBP                      -->
<!-- ========================================== -->
<div id="modalEditWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">✏️ Edit Data Warga Binaan</h3>
            <button onclick="closeModal('modalEditWbp')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="formEditWbp" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nomor Registrasi Lapas *</label>
                    <input type="text" id="edit_no_registrasi" name="no_registrasi" required>
                </div>
                <div class="form-group">
                    <label>NIK Narapidana</label>
                    <input type="text" id="edit_nik" name="nik">
                </div>
            </div>

            <div class="form-group">
                <label>Nama Lengkap Warga Binaan *</label>
                <input type="text" id="edit_nama" name="nama" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Lapas / Rutan *</label>
                    <input type="text" id="edit_lapas_asal" name="lapas_asal" required>
                </div>
                <div class="form-group">
                    <label>Penempatan Kamar/Blok *</label>
                    <input type="text" id="edit_blok_kamar" name="blok_kamar" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nomor Buku Register D</label>
                    <input type="text" id="edit_register_d_number" name="register_d_number">
                </div>
                <div class="form-group">
                    <label>Nama Penjamin / Keluarga</label>
                    <input type="text" id="edit_penjamin_nama" name="penjamin_nama">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Hubungan Penjamin</label>
                    <input type="text" id="edit_penjamin_hubungan" name="penjamin_hubungan">
                </div>
                <div class="form-group">
                    <label>No. HP / WA Penjamin</label>
                    <input type="text" id="edit_penjamin_telepon" name="penjamin_telepon">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Batas Limit Belanja Harian (Rp) *</label>
                    <input type="number" id="edit_limit_harian" name="limit_harian" min="0" step="5000" required>
                </div>
                <div class="form-group">
                    <label>Kontak Keluarga</label>
                    <input type="text" id="edit_kontak_keluarga" name="kontak_keluarga">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Tanggal Masuk Lapas</label>
                    <input type="date" id="edit_tanggal_masuk" name="tanggal_masuk">
                </div>
                <div class="form-group">
                    <label>Perkiraan Tanggal Bebas (Ekspirasi)</label>
                    <input type="date" id="edit_tanggal_ekspirasi" name="tanggal_ekspirasi">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditWbp')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: Sanksi / Register F / Ubah Status -->
<!-- ========================================== -->
<div id="modalStatusWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 500px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">⚖️ Sanksi Indisipliner (Register F) & Status</h3>
            <button onclick="closeModal('modalStatusWbp')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="formStatusWbp" method="POST">
            @csrf
            <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                <div style="font-size: 11.5px; color: var(--text-muted);">Nama Warga Binaan:</div>
                <div id="statusWbpNama" style="font-size: 15px; font-weight: 800; color: #0f172a;">-</div>
            </div>

            <div class="form-group">
                <label>Pilih Status WBP *</label>
                <select id="statusSelect" name="status" required>
                    <option value="aktif">● AKTIF (Boleh Belanja di Kasir Koperasi)</option>
                    <option value="isolasi">⛔ ISOLASI / REGISTER F (Blokir Otomatis Belanja Kasir)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Catatan / Alasan Putusan Sanksi</label>
                <textarea id="statusAlasan" name="alasan_status" rows="3" placeholder="Contoh: Pelanggaran tata tertib blok, perkelahian, register F nomor 12/2026..."></textarea>
            </div>

            <p style="font-size: 11.5px; color: #ef4444; background: #fef2f2; padding: 10px; border-radius: 6px; margin-bottom: 16px;">
                ⚠️ <strong>Penting:</strong> WBP berstatus <strong>Isolasi (Register F)</strong> akan langsung ditolak sistem kasir biometrik desktop bila mencoba berbelanja.
            </p>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalStatusWbp')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Status WBP</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: Pembebasan WBP & Pencairan Saldo   -->
<!-- ========================================== -->
<div id="modalReleaseWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 520px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">🕊️ Proses Pembebasan WBP (Cash Out)</h3>
            <button onclick="closeModal('modalReleaseWbp')" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="formReleaseWbp" method="POST" onsubmit="return confirm('Konfirmasi pembebasan WBP ini dan pencairan seluruh sisa saldo secara tunai?')">
            @csrf
            <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                <div style="font-size: 11.5px; color: var(--text-muted);">Nama Warga Binaan:</div>
                <div id="releaseWbpNama" style="font-size: 15px; font-weight: 800; color: #0f172a;">-</div>
            </div>

            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 16px; text-align: center; margin-bottom: 18px;">
                <div style="font-size: 12px; color: #065f46; font-weight: 700; text-transform: uppercase;">Sisa Saldo Yang Wajib Dicairkan Tunai:</div>
                <div id="releaseWbpSaldo" style="font-size: 26px; font-weight: 800; color: #059669; margin-top: 4px;">Rp 0</div>
                <div style="font-size: 11px; color: #047857; margin-top: 4px;">Diserahkan tunai kepada WBP saat keluar pintu utama lapas.</div>
            </div>

            <div class="form-group">
                <label>Nama Petugas Pengesah / Penyerah Saldo *</label>
                <input type="text" name="petugas_bebas" value="Petugas Subseksi Registrasi" required>
            </div>

            <div class="form-group">
                <label>Dasar Pembebasan & Catatan</label>
                <textarea name="catatan_bebas" rows="2" placeholder="Contoh: Bebas Murni / PB / CB Surat Keputusan Menkumham No..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalReleaseWbp')">Batal</button>
                <button type="submit" class="btn" style="background: #059669; color: #fff;">Sahkan Bebas & Cairkan Saldo</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 5: Mutasi WBP Antar-Lapas             -->
<!-- ========================================== -->
<div id="modalTransferWbp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">🔄 Mutasi Perpindahan WBP Antar-Lapas</h3>
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

<!-- Modal Import Excel WBP (.xlsx) -->
<div id="modalImportExcel" class="modal" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(4px);">
    <div class="card" style="width: 100%; max-width: 580px; margin: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 17px; display: flex; align-items: center; gap: 8px;">
                <span>📊</span> Import Massal WBP dari Microsoft Excel
            </h3>
            <button type="button" onclick="closeModal('modalImportExcel')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.inmates.import_excel') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Download Template Notice -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; margin-bottom: 18px;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <span style="font-size: 24px;">📥</span>
                    <div>
                        <strong style="color: #166534; font-size: 13.5px; display: block; margin-bottom: 4px;">Langkah 1: Gunakan Template Resmi Excel (.xlsx)</strong>
                        <p style="font-size: 12px; color: #15803d; margin: 0 0 10px 0; line-height: 1.5;">
                            Gunakan format template Microsoft Excel standar yang telah dilengkapi petunjuk kolom (No. Registrasi, Nama, Blok/Kamar, dsb).
                        </p>
                        <a href="{{ route('admin.inmates.template_excel') }}" class="btn btn-outline" style="background: white; border-color: #16a34a; color: #16a34a; font-size: 12px; padding: 6px 12px; font-weight: 700;">
                            ⬇️ Unduh Format Template Excel (.xlsx)
                        </a>
                    </div>
                </div>
            </div>

            <!-- Step 2: Upload File -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                    Langkah 2: Pilih File Microsoft Excel (.xlsx / .xls): <span style="color: red;">*</span>
                </label>
                <div style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('excelFileInput').click()">
                    <span style="font-size: 32px; display: block; margin-bottom: 8px;">📂</span>
                    <span id="excelFileName" style="font-size: 13px; font-weight: 600; color: #334155; display: block;">Klik di sini untuk memilih file Excel (.xlsx)</span>
                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 4px;">Hanya format .xlsx atau .xls (Bukan CSV) &bull; Maksimal 10 MB</span>
                    <input type="file" id="excelFileInput" name="excel_file" accept=".xlsx, .xls" style="display: none;" required onchange="handleExcelFileSelect(this)">
                </div>
            </div>

            <!-- Information & Rules -->
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                <strong style="color: #1e40af; font-size: 12px; display: block; margin-bottom: 4px;">ℹ️ Ketentuan Import:</strong>
                <ul style="font-size: 11.5px; color: #1e3a8a; margin: 0; padding-left: 18px; line-height: 1.5;">
                    <li>No. Registrasi harus unik dan belum pernah didaftarkan.</li>
                    <li>WBP yang berhasil diimpor otomatis berstatus <strong>Aktif</strong> dengan Nomor Virtual Account (VA) baru.</li>
                    <li>Jika diisi nominal saldo awal, sistem akan mencatat transaksi setoran awal secara otomatis.</li>
                    <li>Data sidik jari biometrik dapat direkam langsung di POS Kasir setelah WBP masuk sistem.</li>
                </ul>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalImportExcel')">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitImport" style="background: #10b981; border-color: #10b981; font-weight: 700;">
                    🚀 Mulai Import Data WBP
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex';
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function openEditModal(wbp) {
        document.getElementById('formEditWbp').action = "/inmates/" + wbp.id;
        document.getElementById('edit_no_registrasi').value = wbp.no_registrasi || '';
        document.getElementById('edit_register_d_number').value = wbp.register_d_number || '';
        document.getElementById('edit_nik').value = wbp.nik || '';
        document.getElementById('edit_nama').value = wbp.nama || '';
        document.getElementById('edit_penjamin_nama').value = wbp.penjamin_nama || '';
        document.getElementById('edit_penjamin_hubungan').value = wbp.penjamin_hubungan || '';
        document.getElementById('edit_penjamin_telepon').value = wbp.penjamin_telepon || '';
        document.getElementById('edit_lapas_asal').value = wbp.lapas_asal || '';
        document.getElementById('edit_blok_kamar').value = wbp.blok_kamar || '';
        document.getElementById('edit_limit_harian').value = wbp.limit_harian || 100000;
        document.getElementById('edit_kontak_keluarga').value = wbp.kontak_keluarga || '';
        document.getElementById('edit_tanggal_masuk').value = wbp.tanggal_masuk || '';
        document.getElementById('edit_tanggal_ekspirasi').value = wbp.tanggal_ekspirasi || '';
        openModal('modalEditWbp');
    }

    function openStatusModal(id, nama, status, alasan) {
        document.getElementById('formStatusWbp').action = "/inmates/" + id + "/status";
        document.getElementById('statusWbpNama').innerText = nama;
        document.getElementById('statusSelect').value = (status === 'isolasi') ? 'aktif' : 'isolasi';
        document.getElementById('statusAlasan').value = alasan || '';
        openModal('modalStatusWbp');
    }

    function openReleaseModal(id, nama, saldo) {
        document.getElementById('formReleaseWbp').action = "/inmates/" + id + "/release";
        document.getElementById('releaseWbpNama').innerText = nama;
        document.getElementById('releaseWbpSaldo').innerText = "Rp " + Number(saldo).toLocaleString('id-ID');
        openModal('modalReleaseWbp');
    }

    function openTransferModal(id, nama, lapas, blok) {
        document.getElementById('trfInmateId').value = id;
        document.getElementById('trfWbpNama').innerText = nama;
        document.getElementById('trfCurrentLocation').innerText = 'Posisi Saat Ini: ' + lapas + ' (' + blok + ')';
        document.getElementById('trfLapasTujuan').value = lapas;
        openModal('modalTransferWbp');
    }

    function handleExcelFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const nameEl = document.getElementById('excelFileName');
            if (nameEl) {
                nameEl.innerHTML = '📄 <strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(1) + ' KB)';
                nameEl.style.color = '#047857';
            }
        }
    }
</script>
@endsection
