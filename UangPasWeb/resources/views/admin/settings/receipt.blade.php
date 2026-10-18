@extends('layouts.admin')

@section('title', 'Pengaturan Struk Kasir')
@section('page_title', 'Format Struk & Printer Thermal Kasir')
@section('page_desc', 'Kustomisasi Nama Koperasi, Alamat, Pesan Kaki Struk, dan Ukuran Kertas Thermal 58mm / 80mm')

@section('content')
@if(session('success'))
<div class="card" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
    <span>✅</span> {{ session('success') }}
</div>
@endif

<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; align-items: start;">
    <!-- Settings Form -->
    <div class="card" style="padding: 24px; background: #fff;">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">
            ⚙️ Konfigurasi Header & Footer Struk
        </h3>
        <p style="font-size: 12.5px; color: #64748b; margin-bottom: 20px;">
            Informasi di bawah ini otomatis dicetak pada struk belanja kasir di Desktop POS dan Mobile POS.
        </p>

        <form action="{{ route('admin.settings.receipt.update') }}" method="POST">
            @csrf

            <!-- Nama Koperasi / Toko -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    Nama Koperasi / Header Toko:
                </label>
                <input type="text" name="receipt_store_name" id="inputStoreName" 
                       value="{{ old('receipt_store_name', $setting->receipt_store_name ?? 'KOPERASI PENGAYOMAN LAPAS') }}" 
                       required 
                       style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 700; text-transform: uppercase;">
                <span style="font-size: 11px; color: #64748b;">Akan ditampilkan paling atas dengan huruf kapital tebal.</span>
            </div>

            <!-- Alamat Toko -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    Alamat UPT / Lokasi:
                </label>
                <input type="text" name="receipt_address" id="inputAddress" 
                       value="{{ old('receipt_address', $setting->receipt_address ?? 'Jalan Pemasyarakatan No. 1') }}" 
                       style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <!-- Nomor Telepon -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    No. Telepon / Hotline:
                </label>
                <input type="text" name="receipt_phone" id="inputPhone" 
                       value="{{ old('receipt_phone', $setting->receipt_phone ?? '(021) 5523456') }}" 
                       style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <!-- Ukuran Kertas Thermal -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px;">
                    Ukuran Kertas Thermal Printer:
                </label>
                <div style="display: flex; gap: 14px;">
                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; flex: 1; background: {{ ($setting->receipt_paper_width ?? '58mm') === '58mm' ? '#e0f2fe' : '#fff' }};">
                        <input type="radio" name="receipt_paper_width" value="58mm" id="radio58mm" {{ ($setting->receipt_paper_width ?? '58mm') === '58mm' ? 'checked' : '' }}>
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">58 mm (Standar)</div>
                            <div style="font-size: 11px; color: #64748b;">Printer kasir saku / bluetooth mini (32 kolom)</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; flex: 1; background: ($setting->receipt_paper_width ?? '') === '80mm' ? '#e0f2fe' : '#fff' }};">
                        <input type="radio" name="receipt_paper_width" value="80mm" id="radio80mm" {{ ($setting->receipt_paper_width ?? '') === '80mm' ? 'checked' : '' }}>
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">80 mm (Lebar)</div>
                            <div style="font-size: 11px; color: #64748b;">Printer kasir meja ala Indomaret/Epson (42-48 kolom)</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Pesan Kaki Struk (Footer) -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    Pesan Kaki Struk (Footer / Slogan):
                </label>
                <textarea name="receipt_footer" id="inputFooter" rows="3" 
                          style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">{{ old('receipt_footer', $setting->receipt_footer ?? "Terima Kasih Atas Kunjungan Anda\nSimpan Struk Sebagai Bukti Belanja Sah\nUangPas - Zero Cash Solution") }}</textarea>
                <span style="font-size: 11px; color: #64748b;">Gunakan baris baru (Enter) untuk memisahkan baris pesan kaki.</span>
            </div>

            <!-- Opsi Tambahan -->
            <div style="margin-bottom: 24px; padding: 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="receipt_show_inmate_balance" id="checkShowBalance" value="1" {{ ($setting->receipt_show_inmate_balance ?? true) ? 'checked' : '' }}>
                    <div>
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Cetak Sisa Saldo WBP</span>
                        <div style="font-size: 11px; color: #64748b;">Mencetak posisi sisa saldo dompet digital WBP setelah transaksi belanja.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="receipt_auto_print" value="1" {{ ($setting->receipt_auto_print ?? false) ? 'checked' : '' }}>
                    <div>
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Cetak Otomatis Saat Bayar Sukses (Auto-Print)</span>
                        <div style="font-size: 11px; color: #64748b;">Langsung mengirim perintah cetak ke printer kasir tanpa memunculkan dialog konfirmasi.</div>
                    </div>
                </label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 13.5px;">
                    💾 Simpan Pengaturan Struk
                </button>
            </div>
        </form>
    </div>

    <!-- Live Thermal Paper Receipt Simulation -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0;">
                🖨️ Pratinjau Kertas Thermal Kasir (Live)
            </h3>
            <span id="previewPaperLabel" class="pill" style="background: #0f172a; color: #fff; font-size: 11px; font-weight: 700;">
                Kertas: 58 mm
            </span>
        </div>

        <!-- Simulated Thermal Receipt Container -->
        <div style="background: #475569; padding: 20px; border-radius: 12px; display: flex; justify-content: center; box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);">
            <div id="receiptPaper" 
                 style="background: #ffffff; width: 310px; padding: 20px 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.35); font-family: 'Consolas', 'Courier New', monospace; font-size: 11.5px; color: #111827; line-height: 1.35; border-bottom: 6px dashed #cbd5e1; transition: width 0.3s ease;">
                
                <!-- Receipt Header -->
                <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                    <div id="prevStoreName" style="font-size: 13px; font-weight: 900; letter-spacing: 0.5px;">
                        {{ $setting->receipt_store_name ?? 'KOPERASI PENGAYOMAN LAPAS' }}
                    </div>
                    <div id="prevAddress" style="font-size: 10.5px; margin-top: 2px;">
                        {{ $setting->receipt_address ?? 'Jalan Pemasyarakatan No. 1' }}
                    </div>
                    <div id="prevPhone" style="font-size: 10.5px;">
                        Telp: {{ $setting->receipt_phone ?? '(021) 5523456' }}
                    </div>
                </div>

                <!-- Info Transaksi -->
                <div style="border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>No. Invoice:</span>
                        <span style="font-weight: 700;">TRX-{{ date('Ymd') }}-8891</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Waktu:</span>
                        <span>{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Kasir:</span>
                        <span>Kasir POS 1</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Metode:</span>
                        <span style="font-weight: 700;">SIDIK JARI WBP</span>
                    </div>
                    <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dotted #ccc;">
                        <div>WBP: <b>AHMAD FAUZI</b> (BP-10294)</div>
                        <div>Kamar: Blok B - Kamar 04</div>
                    </div>
                </div>

                <!-- Daftar Barang Terbeli -->
                <div style="border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px;">
                    <div style="display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 4px;">
                        <span>ITEM</span>
                        <span>SUBTOTAL</span>
                    </div>

                    <div style="margin-bottom: 4px;">
                        <div>Indomie Goreng Spesial</div>
                        <div style="display: flex; justify-content: space-between; color: #4b5563; font-size: 10.5px;">
                            <span>3 x Rp 3.500</span>
                            <span style="color: #000; font-weight: 700;">Rp 10.500</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 4px;">
                        <div>Kopi Kapal Api Mix 24g</div>
                        <div style="display: flex; justify-content: space-between; color: #4b5563; font-size: 10.5px;">
                            <span>5 x Rp 2.000</span>
                            <span style="color: #000; font-weight: 700;">Rp 10.000</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 4px;">
                        <div>Sabun Mandi Lifebuoy 85g</div>
                        <div style="display: flex; justify-content: space-between; color: #4b5563; font-size: 10.5px;">
                            <span>1 x Rp 4.500</span>
                            <span style="color: #000; font-weight: 700;">Rp 4.500</span>
                        </div>
                    </div>
                </div>

                <!-- Total & Saldo -->
                <div style="border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 900;">
                        <span>TOTAL:</span>
                        <span>Rp 25.000</span>
                    </div>

                    <div id="prevBalanceSection" style="margin-top: 6px; padding-top: 4px; border-top: 1px dotted #ccc; font-size: 11px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Saldo Sebelum:</span>
                            <span>Rp 150.000</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: 800;">
                            <span>SISA SALDO WBP:</span>
                            <span>Rp 125.000</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Text -->
                <div id="prevFooter" style="text-align: center; font-size: 10.5px; white-space: pre-line; line-height: 1.3;">
{{ $setting->receipt_footer ?? "Terima Kasih Atas Kunjungan Anda\nSimpan Struk Sebagai Bukti Belanja Sah\nUangPas - Zero Cash Solution" }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Real-Time Preview Synchronization
    const inputStoreName = document.getElementById('inputStoreName');
    const inputAddress = document.getElementById('inputAddress');
    const inputPhone = document.getElementById('inputPhone');
    const inputFooter = document.getElementById('inputFooter');
    const radio58mm = document.getElementById('radio58mm');
    const radio80mm = document.getElementById('radio80mm');
    const checkShowBalance = document.getElementById('checkShowBalance');

    const prevStoreName = document.getElementById('prevStoreName');
    const prevAddress = document.getElementById('prevAddress');
    const prevPhone = document.getElementById('prevPhone');
    const prevFooter = document.getElementById('prevFooter');
    const prevBalanceSection = document.getElementById('prevBalanceSection');
    const receiptPaper = document.getElementById('receiptPaper');
    const previewPaperLabel = document.getElementById('previewPaperLabel');

    inputStoreName.addEventListener('input', () => {
        prevStoreName.innerText = inputStoreName.value.toUpperCase() || 'KOPERASI PENGAYOMAN LAPAS';
    });

    inputAddress.addEventListener('input', () => {
        prevAddress.innerText = inputAddress.value || '';
    });

    inputPhone.addEventListener('input', () => {
        prevPhone.innerText = inputPhone.value ? 'Telp: ' + inputPhone.value : '';
    });

    inputFooter.addEventListener('input', () => {
        prevFooter.innerText = inputFooter.value || '';
    });

    checkShowBalance.addEventListener('change', () => {
        prevBalanceSection.style.display = checkShowBalance.checked ? 'block' : 'none';
    });

    function updatePaperWidth() {
        if (radio80mm.checked) {
            receiptPaper.style.width = '420px';
            previewPaperLabel.innerText = 'Kertas: 80 mm (Lebar)';
        } else {
            receiptPaper.style.width = '310px';
            previewPaperLabel.innerText = 'Kertas: 58 mm (Standar)';
        }
    }

    radio58mm.addEventListener('change', updatePaperWidth);
    radio80mm.addEventListener('change', updatePaperWidth);
    updatePaperWidth();
</script>
@endsection
