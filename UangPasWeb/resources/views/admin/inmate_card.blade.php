<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Anggota WBP - {{ $inmate->nama }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .action-bar {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-print { background: #0284c7; color: #fff; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3); }
        .btn-back { background: #e2e8f0; color: #1e293b; }

        /* Card Container (Standard ID Card Aspect Ratio 85.6mm x 53.98mm) */
        .id-card {
            width: 480px;
            height: 300px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 16px;
            color: #fff;
            padding: 22px 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .id-card::after {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title h3 {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #38bdf8;
        }

        .header-title p {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
        }

        .badge-koperasi {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .card-body {
            display: flex;
            gap: 18px;
        }

        .photo-box {
            width: 100px;
            height: 125px;
            background: #090d16;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            position: relative;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            font-size: 40px;
            color: #475569;
        }

        .details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .detail-row {
            margin-bottom: 6px;
        }

        .detail-label {
            font-size: 9.5px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .card-footer {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 10px;
        }

        .barcode-section {
            display: flex;
            flex-direction: column;
        }

        /* Barcode 128 Font or Representation */
        .barcode-visual {
            font-family: 'Libre Barcode 128', cursive, monospace;
            font-size: 38px;
            line-height: 38px;
            letter-spacing: 2px;
            color: #fff;
        }

        .barcode-code {
            font-size: 10px;
            font-family: monospace;
            letter-spacing: 2px;
            color: #94a3b8;
            font-weight: 700;
        }

        .va-info {
            text-align: right;
        }

        .va-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }

        .va-num {
            font-family: monospace;
            font-size: 13px;
            font-weight: 800;
            color: #38bdf8;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .action-bar {
                display: none;
            }
            .id-card {
                box-shadow: none;
                border: 1px solid #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <a href="{{ route('admin.inmates') }}" class="btn btn-back">⬅ Kembali</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Kartu ID WBP</button>
    </div>

    <!-- Official Inmate Digital Card -->
    <div class="id-card">
        <div class="card-header">
            <div class="header-left">
                <span style="font-size: 20px;">🇮🇩</span>
                <div class="header-title">
                    <h3>KARTU DOMPET DIGITAL WBP</h3>
                    <p>{{ $setting->upt_name ?? 'Lapas Kelas I Tangerang' }}</p>
                </div>
            </div>
            <span class="badge-koperasi">Zero Cash POS</span>
        </div>

        <div class="card-body">
            <div class="photo-box">
                @if(!empty($inmate->fingerprint_image))
                    <img src="data:image/png;base64,{{ $inmate->fingerprint_image }}" alt="Biometrik">
                @else
                    <div class="photo-placeholder">👤</div>
                    <span style="font-size: 8px; color: #64748b; margin-top: 4px;">ID BIOMETRIK</span>
                @endif
            </div>

            <div class="details">
                <div class="detail-row">
                    <div class="detail-label">Nama Warga Binaan</div>
                    <div class="detail-value" style="font-size: 15px; color: #f8fafc;">{{ $inmate->nama }}</div>
                </div>
                <div style="display: flex; gap: 16px;">
                    <div class="detail-row">
                        <div class="detail-label">Nomor Registrasi</div>
                        <div class="detail-value" style="font-family: monospace; color: #38bdf8;">{{ $inmate->no_registrasi }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Blok / Kamar</div>
                        <div class="detail-value">{{ $inmate->blok_kamar }}</div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Limit Belanja Harian</div>
                    <div class="detail-value" style="color: #34d399;">Rp {{ number_format($inmate->limit_harian, 0, ',', '.') }} / hari</div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="barcode-section">
                <!-- Barcode Representation of Registration Number -->
                <div class="barcode-visual">*{{ $inmate->no_registrasi }}*</div>
                <div class="barcode-code">{{ $inmate->no_registrasi }}</div>
            </div>

            <div class="va-info">
                <div class="va-label">VA Transfer Keluarga</div>
                <div class="va-num">{{ $inmate->va_number ?? '-' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
