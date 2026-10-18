<?php

namespace App\Services;

use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\UptSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InmateExcelService
{
    /**
     * Generate standard Microsoft Excel (.xlsx) template for bulk importing inmates
     */
    public function generateTemplate(string $outputPath): void
    {
        $setting = UptSetting::current();
        $uptName = $setting ? $setting->upt_name : 'Lapas Kelas I';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import WBP');

        // Page setup
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA - DITJEN PEMASYARAKATAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'TEMPLATE RESMI IMPORT DATA WARGA BINAAN PEMASYARAKATAN (WBP) - ' . strtoupper($uptName));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0284C7'));

        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'Petunjuk: Kolom bertanda (*) WAJIB diisi. No. Registrasi harus unik. Saldo Awal & Limit Harian berupa angka tanpa titik/koma. Hapus baris contoh sebelum mengunggah jika tidak diperlukan.');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // Table Column Headers
        $headers = [
            'A' => ['title' => 'No. Registrasi (*)', 'field' => 'no_registrasi', 'width' => 20],
            'B' => ['title' => 'Nama Lengkap WBP (*)', 'field' => 'nama', 'width' => 32],
            'C' => ['title' => 'NIK (16 Digit)', 'field' => 'nik', 'width' => 20],
            'D' => ['title' => 'Kamar / Blok (*)', 'field' => 'blok_kamar', 'width' => 22],
            'E' => ['title' => 'Lapas Asal / UPT', 'field' => 'lapas_asal', 'width' => 26],
            'F' => ['title' => 'Saldo Awal (Rp)', 'field' => 'saldo_awal', 'width' => 18],
            'G' => ['title' => 'Limit Harian (Rp)', 'field' => 'limit_harian', 'width' => 18],
            'H' => ['title' => 'Tanggal Masuk (YYYY-MM-DD)', 'field' => 'tanggal_masuk', 'width' => 25],
            'I' => ['title' => 'Kontak Keluarga / Wali', 'field' => 'kontak_keluarga', 'width' => 28],
        ];

        $headerRow = 5;
        foreach ($headers as $col => $info) {
            $cellCoord = $col . $headerRow;
            $sheet->setCellValue($cellCoord, $info['title']);
            $sheet->getColumnDimension($col)->setWidth($info['width']);
        }

        // Style header row
        $headerRange = 'A5:I5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B'); // Slate 800
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Sample Data Rows (Row 6 - 8)
        $samples = [
            [
                'no_reg' => 'WBP-' . date('Y') . '-0101',
                'nama' => 'Bambang Pamungkas bin Soeryo',
                'nik' => '3201123456780001',
                'kamar' => 'Blok B - Kamar 05',
                'lapas' => $uptName,
                'saldo' => 250000,
                'limit' => 100000,
                'tgl_masuk' => date('Y-m-d'),
                'kontak' => '081234567890 (Istri)',
            ],
            [
                'no_reg' => 'WBP-' . date('Y') . '-0102',
                'nama' => 'Dedi Kurniawan bin Herman',
                'nik' => '3201123456780002',
                'kamar' => 'Blok A - Kamar 02',
                'lapas' => $uptName,
                'saldo' => 500000,
                'limit' => 100000,
                'tgl_masuk' => date('Y-m-d'),
                'kontak' => '085712345678 (Ayah)',
            ],
            [
                'no_reg' => 'WBP-' . date('Y') . '-0103',
                'nama' => 'Joko Susilo bin Marwan',
                'nik' => '3201123456780003',
                'kamar' => 'Blok Mapenaling',
                'lapas' => $uptName,
                'saldo' => 0,
                'limit' => 100000,
                'tgl_masuk' => date('Y-m-d'),
                'kontak' => '089612345678 (Adik)',
            ],
        ];

        $r = 6;
        foreach ($samples as $s) {
            $sheet->setCellValueExplicit('A' . $r, $s['no_reg'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $r, $s['nama'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $r, $s['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $r, $s['kamar'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $r, $s['lapas'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $r, $s['saldo']);
            $sheet->setCellValue('G' . $r, $s['limit']);
            $sheet->setCellValueExplicit('H' . $r, $s['tgl_masuk'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('I' . $r, $s['kontak'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Numbers format
            $sheet->getStyle('F' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $r)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getRowDimension($r)->setRowHeight(22);
            $r++;
        }

        // Borders for table
        $dataRange = 'A5:I' . ($r - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);
    }

    /**
     * Parse and import inmates from uploaded .xlsx Excel file
     */
    public function importInmates(string $filePath): array
    {
        $setting = UptSetting::current();
        $defaultUpt = $setting ? $setting->upt_name : 'Lapas';

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File Excel kosong atau tidak dapat dibaca.',
                'imported' => 0,
                'skipped' => 0,
                'errors' => []
            ];
        }

        // Identify header row (find row containing 'no. registrasi' or 'no_registrasi' column header)
        $headerRowIndex = null;
        $columnMap = [];

        foreach ($rows as $rowIndex => $cols) {
            foreach ($cols as $colLetter => $val) {
                $cleanVal = strtolower(trim((string)$val));
                // Only match cell if it looks like a column title (short, starts with no, contains registrasi)
                if (strlen($cleanVal) <= 30 && (str_starts_with($cleanVal, 'no.') || str_starts_with($cleanVal, 'no_') || str_starts_with($cleanVal, 'no ')) && str_contains($cleanVal, 'registrasi')) {
                    $headerRowIndex = $rowIndex;
                    break 2;
                }
            }
        }

        if ($headerRowIndex === null) {
            // Fallback: assume header is row 5
            $headerRowIndex = 5;
        }

        // Map column letters to fields based on header text
        $headerCols = $rows[$headerRowIndex] ?? [];
        foreach ($headerCols as $colLetter => $val) {
            $headerText = strtolower(trim((string)$val));
            if (str_contains($headerText, 'registrasi')) {
                $columnMap['no_registrasi'] = $colLetter;
            } elseif (str_contains($headerText, 'nama')) {
                $columnMap['nama'] = $colLetter;
            } elseif (str_contains($headerText, 'nik')) {
                $columnMap['nik'] = $colLetter;
            } elseif (str_contains($headerText, 'kamar') || str_contains($headerText, 'blok')) {
                $columnMap['blok_kamar'] = $colLetter;
            } elseif (str_contains($headerText, 'lapas') || str_contains($headerText, 'upt')) {
                $columnMap['lapas_asal'] = $colLetter;
            } elseif (str_contains($headerText, 'saldo')) {
                $columnMap['saldo_awal'] = $colLetter;
            } elseif (str_contains($headerText, 'limit')) {
                $columnMap['limit_harian'] = $colLetter;
            } elseif (str_contains($headerText, 'masuk') || str_contains($headerText, 'tanggal')) {
                $columnMap['tanggal_masuk'] = $colLetter;
            } elseif (str_contains($headerText, 'kontak') || str_contains($headerText, 'keluarga')) {
                $columnMap['kontak_keluarga'] = $colLetter;
            }
        }

        // Default standard positional mapping if not found by name
        if (!isset($columnMap['no_registrasi'])) $columnMap['no_registrasi'] = 'A';
        if (!isset($columnMap['nama'])) $columnMap['nama'] = 'B';
        if (!isset($columnMap['nik'])) $columnMap['nik'] = 'C';
        if (!isset($columnMap['blok_kamar'])) $columnMap['blok_kamar'] = 'D';
        if (!isset($columnMap['lapas_asal'])) $columnMap['lapas_asal'] = 'E';
        if (!isset($columnMap['saldo_awal'])) $columnMap['saldo_awal'] = 'F';
        if (!isset($columnMap['limit_harian'])) $columnMap['limit_harian'] = 'G';
        if (!isset($columnMap['tanggal_masuk'])) $columnMap['tanggal_masuk'] = 'H';
        if (!isset($columnMap['kontak_keluarga'])) $columnMap['kontak_keluarga'] = 'I';

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $cols) {
                if ($rowIndex <= $headerRowIndex) {
                    continue; // Skip header and above
                }

                $noReg = trim((string)($cols[$columnMap['no_registrasi']] ?? ''));
                $nama = trim((string)($cols[$columnMap['nama']] ?? ''));

                // Skip completely empty rows
                if (empty($noReg) && empty($nama)) {
                    continue;
                }

                // Skip header text if repeated
                if (str_contains(strtolower($noReg), 'registrasi') || str_contains(strtolower($nama), 'nama lengkap')) {
                    continue;
                }

                // Validation
                if (empty($noReg)) {
                    $errors[] = "Baris {$rowIndex}: Nomor Registrasi kosong.";
                    $skipped++;
                    continue;
                }

                if (empty($nama)) {
                    $errors[] = "Baris {$rowIndex} ({$noReg}): Nama WBP kosong.";
                    $skipped++;
                    continue;
                }

                // Check if inmate with this registration already exists
                if (Inmate::where('no_registrasi', $noReg)->exists()) {
                    $errors[] = "Baris {$rowIndex} ({$noReg}): Sudah terdaftar di sistem (dilewati).";
                    $skipped++;
                    continue;
                }

                $nik = trim((string)($cols[$columnMap['nik']] ?? ''));
                $blokKamar = trim((string)($cols[$columnMap['blok_kamar']] ?? ''));
                if (empty($blokKamar)) $blokKamar = 'Blok Mapenaling';

                $lapasAsal = trim((string)($cols[$columnMap['lapas_asal']] ?? ''));
                if (empty($lapasAsal)) $lapasAsal = $defaultUpt;

                $rawSaldo = str_replace(['Rp', '.', ',', ' '], '', (string)($cols[$columnMap['saldo_awal']] ?? '0'));
                $saldoAwal = is_numeric($rawSaldo) ? (float)$rawSaldo : 0.0;

                $rawLimit = str_replace(['Rp', '.', ',', ' '], '', (string)($cols[$columnMap['limit_harian']] ?? '100000'));
                $limitHarian = is_numeric($rawLimit) && (float)$rawLimit > 0 ? (float)$rawLimit : 100000.0;

                $tanggalMasuk = trim((string)($cols[$columnMap['tanggal_masuk']] ?? ''));
                if (empty($tanggalMasuk) || !strtotime($tanggalMasuk)) {
                    $tanggalMasuk = date('Y-m-d');
                } else {
                    $tanggalMasuk = date('Y-m-d', strtotime($tanggalMasuk));
                }

                $kontakKeluarga = trim((string)($cols[$columnMap['kontak_keluarga']] ?? ''));

                // Generate unique VA number
                $vaNumber = '88001' . rand(100000000, 999999999);
                while (Inmate::where('va_number', $vaNumber)->exists()) {
                    $vaNumber = '88001' . rand(100000000, 999999999);
                }

                $inmate = Inmate::create([
                    'no_registrasi' => $noReg,
                    'nama' => $nama,
                    'nik' => !empty($nik) ? $nik : null,
                    'blok_kamar' => $blokKamar,
                    'lapas_asal' => $lapasAsal,
                    'status' => 'aktif',
                    'saldo' => $saldoAwal,
                    'va_number' => $vaNumber,
                    'limit_harian' => $limitHarian,
                    'tanggal_masuk' => $tanggalMasuk,
                    'kontak_keluarga' => !empty($kontakKeluarga) ? $kontakKeluarga : null,
                ]);

                // Record initial balance transaction if positive
                if ($saldoAwal > 0) {
                    Transaction::create([
                        'invoice_no' => 'INIT-' . date('YmdHis') . '-' . strtoupper(Str::random(4)),
                        'inmate_id' => $inmate->id,
                        'type' => 'topup',
                        'amount' => $saldoAwal,
                        'balance_before' => 0,
                        'balance_after' => $saldoAwal,
                        'payment_method' => 'cash_counter',
                        'reference_id' => 'IMPORT-EXCEL',
                        'status' => 'success',
                        'cashier_name' => 'Import Excel Massal',
                        'notes' => 'Saldo awal registrasi massal via Excel',
                    ]);
                }

                $imported++;
            }

            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'message' => "Berhasil mengimpor {$imported} Warga Binaan dari file Excel." . ($skipped > 0 ? " ({$skipped} data dilewati)." : "")
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'skipped' => $skipped,
                'errors' => [$e->getMessage()],
                'message' => 'Terjadi kesalahan saat mengimpor Excel: ' . $e->getMessage()
            ];
        }
    }
}
