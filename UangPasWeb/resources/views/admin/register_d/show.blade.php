@extends('layouts.admin')

@section('title', 'Buku Register D - ' . $inmate->nama)
@section('page_title', 'Buku Mutasi Register D Individu')
@section('page_desc', 'Buku Tabungan Titipan Uang Nasabah Warga Binaan: ' . $inmate->nama . ' (' . ($inmate->register_d_number ?? 'REG-D-' . $inmate->id) . ')')

@section('content')
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <a href="{{ route('admin.register_d.index') }}" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
        &larr; Kembali ke Buku Besar Register D
    </a>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.register_d.statement', ['id' => $inmate->id, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" target="_blank" class="btn btn-outline" style="border-color: #0284c7; color: #0284c7; font-weight: 700;">
            <span>🖨️</span> Cetak Rekening Koran Resmi (PDF)
        </a>
        <a href="{{ route('admin.register_d.passbook', $inmate->id) }}" target="_blank" class="btn btn-outline" style="font-weight: 700;">
            <span>💳</span> Cetak Buku Saku (Passbook)
        </a>
        @if($inmate->status === 'aktif')
            <a href="{{ route('admin.topup') }}" class="btn btn-success" style="font-weight: 700;">
                <span>📥</span> Setor Titipan Loket
            </a>
            <button type="button" class="btn btn-outline" style="border-color: #ef4444; color: #ef4444; font-weight: 700;" onclick="openReleaseModal()">
                <span>💸</span> Cairkan Saldo (Bebas)
            </button>
        @elseif($inmate->status === 'bebas')
            <a href="{{ route('admin.register_d.release_receipt', $inmate->id) }}" target="_blank" class="btn btn-outline" style="border-color: #ef4444; color: #ef4444; font-weight: 700;">
                <span>📄</span> Cetak Kwitansi Bebas
            </a>
        @endif
    </div>
</div>

@if(session('print_release_id'))
    <div style="background: #eff6ff; border: 2px solid #3b82f6; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 24px;">📄</span>
            <div>
                <strong style="color: #1e40af; font-size: 14px;">Warga Binaan Berhasil Diproses Bebas!</strong>
                <div style="font-size: 12.5px; color: #3b82f6;">Silakan cetak Berita Acara dan Kwitansi Pengeluaran Sisa Uang Titipan Register D.</div>
            </div>
        </div>
        <a href="{{ route('admin.register_d.release_receipt', session('print_release_id')) }}" target="_blank" class="btn btn-primary" style="background: #1d4ed8; border-color: #1d4ed8;">
            🖨️ Cetak Kwitansi Bebas Sekarang
        </a>
    </div>
@endif

<div class="grid-2" style="margin-bottom: 24px;">
    <!-- Profil Nasabah Card -->
    <div class="card">
        <div style="display: flex; gap: 18px; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; flex-shrink: 0;">
                {{ substr($inmate->nama, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h2 style="font-size: 18px; font-weight: 800; color: #0f172a;">{{ $inmate->nama }}</h2>
                    @if($inmate->status === 'aktif')
                        <span class="pill pill-success">AKTIF</span>
                    @elseif($inmate->status === 'isolasi')
                        <span class="pill pill-danger">ISOLASI (REG F)</span>
                    @else
                        <span class="pill pill-outline">{{ strtoupper($inmate->status) }}</span>
                    @endif
                </div>
                <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px;">
                    No. Reg Lapas: <strong>{{ $inmate->no_registrasi }}</strong> &bull; NIK: {{ $inmate->nik ?? '-' }}
                </div>
                <div style="margin-top: 6px;">
                    <span class="pill pill-primary">{{ $inmate->blok_kamar }}</span>
                    <span class="pill pill-outline">UPT: {{ $inmate->lapas_asal }}</span>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Nomor Buku Register D</span>
                <span style="font-family: monospace; font-weight: 800; color: #0284c7;">
                    {{ $inmate->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($inmate->id, 4, '0', STR_PAD_LEFT)) }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Nomor Rekening Virtual Account</span>
                <span style="font-family: monospace; font-weight: 700; color: #0f172a;">{{ $inmate->va_number }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Nama Penjamin / Keluarga</span>
                <strong>{{ $inmate->penjamin_nama ?? ($inmate->kontak_keluarga ?? 'Belum ada data') }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Hubungan & No. HP Penjamin</span>
                <span>{{ $inmate->penjamin_hubungan ?? 'Keluarga' }} ({{ $inmate->penjamin_telepon ?? '-' }})</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Batas Belanja Harian (Limit)</span>
                <strong>Rp {{ number_format($inmate->limit_harian, 0, ',', '.') }} / hari</strong>
            </div>
        </div>
    </div>

    <!-- Saldo & Ringkasan Mutasi Card -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 22px; border-radius: var(--radius); border: 1px solid #334155;">
            <div style="font-size: 11.5px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                Saldo Titipan Mengendap (Register D)
            </div>
            <div style="font-size: 34px; font-weight: 800; color: #38bdf8; margin: 8px 0;">
                Rp {{ number_format($inmate->saldo, 0, ',', '.') }}
            </div>
            <div style="font-size: 12.5px; color: #cbd5e1; display: flex; justify-content: space-between; border-top: 1px solid #334155; padding-top: 10px; margin-top: 8px;">
                <span>Total Setoran (Periode): <strong style="color: #34d399;">+ Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong></span>
                <span>Total Pengeluaran: <strong style="color: #f87171;">- Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- Filter Periode Transaksi -->
        <div class="card" style="padding: 16px 20px;">
            <form action="{{ route('admin.register_d.show', $inmate->id) }}" method="GET" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0;">
                <span style="font-size: 12.5px; font-weight: 700; color: #475569;">Periode:</span>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" style="width: auto; padding: 6px 10px; font-size: 12.5px;">
                <span style="font-size: 12px; color: var(--text-muted);">s.d.</span>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" style="width: auto; padding: 6px 10px; font-size: 12.5px;">
                <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 12.5px;">
                    Filter
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Mutasi Ledger Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--card-border);">
        <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <span>📑</span> Buku Catatan Mutasi Uang Titipan (Debet, Kredit & Saldo Berjalan)
        </h3>
        <span style="font-size: 12px; color: var(--text-muted);">
            Periode: <strong>{{ $startDate->format('d/m/Y') }}</strong> s.d. <strong>{{ $endDate->format('d/m/Y') }}</strong>
        </span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="width: 140px;">Waktu & Tanggal</th>
                    <th>No. Bukti / Invoice</th>
                    <th>Uraian / Keperluan Transaksi</th>
                    <th>Pihak / Penyetor / Kasir</th>
                    <th style="text-align: right; color: #ef4444;">Debet (-)</th>
                    <th style="text-align: right; color: #10b981;">Kredit (+)</th>
                    <th style="text-align: right;">Saldo Berjalan</th>
                    <th style="text-align: center; width: 70px;">Slip</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $index => $trx)
                <tr>
                    <td style="text-align: center; font-size: 12px; color: var(--text-muted);">{{ $index + 1 }}</td>
                    <td style="font-size: 12.5px;">
                        <strong>{{ $trx->created_at->format('d/m/Y') }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $trx->created_at->format('H:i') }} WIB</div>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 12px; font-weight: 700; color: #334155;">{{ $trx->invoice_no }}</span>
                        @if($trx->receipt_number)
                            <div style="font-size: 11px; color: var(--text-muted);">Kwt: {{ $trx->receipt_number }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">
                            @if($trx->type === 'topup')
                                <span style="color: #10b981;">[KREDIT]</span> 
                                {{ $trx->payment_method === 'bank_va' ? 'Kiriman Transfer Virtual Account' : 'Setoran Uang Titipan Loket' }}
                            @elseif($trx->type === 'transfer_out')
                                <span style="color: #ef4444;">[DEBET]</span> Pencairan Sisa Uang Titipan (Bebas)
                            @else
                                <span style="color: #ef4444;">[DEBET]</span> Belanja Kantin / Layanan
                            @endif
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                            {{ $trx->notes ?? '-' }}
                        </div>
                    </td>
                    <td style="font-size: 12px;">
                        @if($trx->sender_name)
                            <strong>{{ $trx->sender_name }}</strong> ({{ $trx->sender_relation ?? 'Keluarga' }})
                        @else
                            {{ $trx->cashier_name ?? $trx->officer_name ?? '-' }}
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #ef4444; font-size: 13.5px;">
                        @if(in_array($trx->type, ['purchase', 'transfer_out']))
                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #10b981; font-size: 13.5px;">
                        @if($trx->type === 'topup')
                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a; font-size: 14px;">
                        Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        @if($trx->type === 'topup')
                            <a href="{{ route('admin.register_d.receipt', $trx->id) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px;" title="Cetak Kuitansi Loket">
                                🧾
                            </a>
                        @else
                            <a href="{{ route('admin.transactions.receipt', $trx->id) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px;" title="Cetak Struk Transaksi">
                                🧾
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 36px;">
                        Belum ada mutasi transaksi pada rentang periode yang dipilih.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($transactions->isNotEmpty())
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td colspan="5" style="text-align: right; padding: 14px;">TOTAL PERIODE INI:</td>
                    <td style="text-align: right; color: #ef4444; padding: 14px;">
                        - Rp {{ number_format($totalDebit, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #10b981; padding: 14px;">
                        + Rp {{ number_format($totalKredit, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #0284c7; padding: 14px; font-size: 15px;">
                        Rp {{ number_format($inmate->saldo, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@if($inmate->status === 'aktif')
<!-- Modal Cairkan Saldo Bebas -->
<div id="modalRelease" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">💸 Pencairan Sisa Saldo & Pembebasan WBP</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
            Sisa saldo titipan WBP <strong>{{ $inmate->nama }}</strong> sebesar <strong style="color: #0284c7;">Rp {{ number_format($inmate->saldo, 0, ',', '.') }}</strong> akan dicairkan secara tunai/transfer, akun ditutup, dan status diubah menjadi <strong>BEBAS</strong>.
        </p>
        <form action="{{ route('admin.inmates.release', $inmate->id) }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Nama Petugas Pembebasan / Loket:</label>
                <input type="text" name="petugas_bebas" value="{{ auth()->user()->name ?? 'Petugas Registrasi' }}" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Dasar / Catatan Pembebasan:</label>
                <input type="text" name="catatan_bebas" placeholder="Contoh: SK Pembebasan Bersyarat No. W10.PAS..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px;" required>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeReleaseModal()" style="padding: 8px 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #dc2626; border-color: #dc2626; padding: 8px 16px;">
                    Konfirmasi Pencairan Dana
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    function openReleaseModal() {
        document.getElementById('modalRelease').style.display = 'flex';
    }
    function closeReleaseModal() {
        document.getElementById('modalRelease').style.display = 'none';
    }
</script>
@endif
@endsection
