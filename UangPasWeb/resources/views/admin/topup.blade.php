@extends('layouts.admin')

@section('title', 'Loket Top Up & Bank VA')
@section('page_title', 'Loket Top-Up & Payment Gateway Bank')
@section('page_desc', 'Setoran Tunai Keluarga di Loket Kunjungan & Integrasi Virtual Account Bank')

@section('content')
<div class="grid-2">
    <!-- Form 1: Loket Setor Tunai Kunjungan -->
    <div class="card">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                🏢
            </div>
            <div>
                <h2 style="font-size: 16px; font-weight: 700;">Loket Setor Tunai (Kunjungan Lapas)</h2>
                <p style="font-size: 12px; color: var(--text-muted);">Pencatatan uang titipan fisik dari keluarga saat membesuk WBP</p>
            </div>
        </div>

        <form action="{{ route('admin.topup.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Pilih Warga Binaan Pemasyarakatan (WBP) *</label>
                <select name="inmate_id" required>
                    <option value="">-- Pilih WBP --</option>
                    @foreach($inmates as $wbp)
                        <option value="{{ $wbp->id }}">
                            {{ $wbp->nama }} ({{ $wbp->no_registrasi }}) &bull; Saldo: Rp {{ number_format($wbp->saldo, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Nominal Setoran Tunai (Rp) *</label>
                <input type="number" name="amount" placeholder="Contoh: 100000" min="5000" step="5000" required>
                <div style="display: flex; gap: 6px; margin-top: 8px;">
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="document.getElementsByName('amount')[0].value=50000">Rp 50.000</button>
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="document.getElementsByName('amount')[0].value=100000">Rp 100.000</button>
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="document.getElementsByName('amount')[0].value=200000">Rp 200.000</button>
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="document.getElementsByName('amount')[0].value=500000">Rp 500.000</button>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nama Keluarga Penyetor *</label>
                    <input type="text" name="penyetor" placeholder="Nama keluarga" required>
                </div>
                <div class="form-group">
                    <label>Hubungan Keluarga</label>
                    <input type="text" name="hubungan" placeholder="Istri, Ibu, Ayah, Anak...">
                </div>
            </div>

            <div class="form-group">
                <label>Catatan Tambahan</label>
                <input type="text" name="notes" placeholder="No. identitas KTP pembesuk / titipan pesan">
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; padding: 12px; justify-content: center; font-size: 14px;">
                <span>📥</span> Simpan Setoran Loket & Cetak Tanda Terima
            </button>
        </form>
    </div>

    <!-- Form 2: Simulator Payment Gateway / Bank VA Webhook -->
    <div class="card">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(2, 132, 199, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                ⚡
            </div>
            <div>
                <h2 style="font-size: 16px; font-weight: 700;">Simulasi Payment Gateway / Bank VA</h2>
                <p style="font-size: 12px; color: var(--text-muted);">Uji coba transfer online keluarga tanpa datang ke Lapas (Midtrans/Xendit/Bank)</p>
            </div>
        </div>

        <form id="formSimulateGateway" onsubmit="simulateBankTransfer(event)">
            <div class="form-group">
                <label>Pilih WBP Penerima (Nomor Virtual Account) *</label>
                <select id="simVaSelect" onchange="updateVaInput()" required>
                    <option value="">-- Pilih WBP --</option>
                    @foreach($inmates as $wbp)
                        <option value="{{ $wbp->va_number }}" data-nama="{{ $wbp->nama }}" data-saldo="{{ $wbp->saldo }}">
                            {{ $wbp->nama }} &bull; VA: {{ $wbp->va_number }} (Saldo: Rp {{ number_format($wbp->saldo, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Nomor Virtual Account</label>
                    <input type="text" id="simVaNumber" readonly style="background: #f8fafc; font-family: monospace; font-weight: 700; color: #0284c7;">
                </div>
                <div class="form-group">
                    <label>Kanal Bank / Metode</label>
                    <select id="simBank">
                        <option value="BCA">Bank BCA Virtual Account</option>
                        <option value="BRI">Bank BRI (BRIVA)</option>
                        <option value="Mandiri">Bank Mandiri Livin</option>
                        <option value="BNI">Bank BNI VA</option>
                        <option value="QRIS">QRIS All Payment</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nominal Transfer Keluarga (Rp) *</label>
                <input type="number" id="simAmount" value="150000" min="10000" step="5000" required>
            </div>

            <div style="background: #f0fdf4; border: 1px dashed #86efac; border-radius: 8px; padding: 12px; font-size: 12px; color: #166534; margin-bottom: 16px;">
                💡 <strong>Webhook Realtime</strong>: Mengirimkan HTTP POST ke <code>/api/v1/webhook/payment-gateway</code> persis seperti notifikasi server bank saat keluarga selesai transfer di ATM/Mobile Banking.
            </div>

            <button type="submit" id="btnSimulate" class="btn btn-primary" style="width: 100%; padding: 12px; justify-content: center; font-size: 14px;">
                <span>🚀</span> Simulasikan Pembayaran Bank (Kirim Webhook)
            </button>

            <div id="simResult" style="display: none; margin-top: 14px; padding: 12px; border-radius: 8px; font-size: 13px;"></div>
        </form>
    </div>
</div>

<!-- Riwayat Top Up Terakhir -->
<div class="card" style="margin-top: 28px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">
        📑 Riwayat Top-Up Terkini (Loket & Gateway Bank)
    </h3>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice / Referensi</th>
                    <th>Warga Binaan</th>
                    <th>Waktu</th>
                    <th>Kanal Pembayaran</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                    <th>Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTopups as $t)
                <tr>
                    <td>
                        <strong>{{ $t->invoice_no }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">Ref: {{ $t->reference_id ?? '-' }}</div>
                    </td>
                    <td>
                        <strong>{{ $t->inmate->nama ?? '-' }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $t->inmate->blok_kamar ?? '-' }}</div>
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($t->payment_method == 'bank_va')
                            <span class="pill pill-primary">Virtual Account</span>
                        @else
                            <span class="pill pill-success">Loket Tunai</span>
                        @endif
                    </td>
                    <td style="font-size: 12px;">{{ $t->notes }}</td>
                    <td style="font-weight: 700; color: #10b981;">
                        + Rp {{ number_format($t->amount, 0, ',', '.') }}
                    </td>
                    <td style="font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($t->balance_after, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">Belum ada riwayat top up.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateVaInput() {
        const select = document.getElementById('simVaSelect');
        document.getElementById('simVaNumber').value = select.value;
    }

    async function simulateBankTransfer(e) {
        e.preventDefault();
        const vaNumber = document.getElementById('simVaNumber').value;
        const amount = document.getElementById('simAmount').value;
        const bank = document.getElementById('simBank').value;
        const resultBox = document.getElementById('simResult');
        const btn = document.getElementById('btnSimulate');

        if (!vaNumber) {
            alert('Silakan pilih WBP penerima terlebih dahulu.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '⏳ Mengirim Notifikasi Bank...';

        try {
            const res = await fetch('/api/v1/webhook/payment-gateway', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    va_number: vaNumber,
                    amount: parseFloat(amount),
                    bank: bank,
                    status: 'settlement',
                    reference_id: 'SIM-' + Date.now()
                })
            });

            const data = await res.json();
            resultBox.style.display = 'block';

            if (data.success) {
                resultBox.style.background = '#ecfdf5';
                resultBox.style.border = '1px solid #10b981';
                resultBox.style.color = '#065f46';
                resultBox.innerHTML = '<strong>✅ Sukses!</strong> ' + data.message + '<br>WBP: <strong>' + data.data.wbp_nama + '</strong> &bull; Saldo Baru: <strong>Rp ' + Number(data.data.new_balance).toLocaleString('id-ID') + '</strong>';
                setTimeout(() => window.location.reload(), 2000);
            } else {
                resultBox.style.background = '#fef2f2';
                resultBox.style.border = '1px solid #ef4444';
                resultBox.style.color = '#991b1b';
                resultBox.innerHTML = '<strong>❌ Gagal:</strong> ' + data.message;
            }
        } catch (err) {
            resultBox.style.display = 'block';
            resultBox.style.background = '#fef2f2';
            resultBox.style.border = '1px solid #ef4444';
            resultBox.style.color = '#991b1b';
            resultBox.innerHTML = '<strong>Error koneksi:</strong> ' + err.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>🚀</span> Simulasikan Pembayaran Bank (Kirim Webhook)';
        }
    }
</script>
@endsection
