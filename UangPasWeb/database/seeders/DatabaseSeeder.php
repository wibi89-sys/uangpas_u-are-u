<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inmate;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        User::updateOrCreate(
            ['email' => 'admin@lapas.go.id'],
            [
                'name' => 'Petugas Koperasi Lapas',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Sample Inmates / WBP
        $inmates = [
            [
                'no_registrasi' => 'WBP-2026-0001',
                'nik' => '3201123456780001',
                'nama' => 'Budi Santoso bin Suhendar',
                'lapas_asal' => 'Lapas Kelas I',
                'blok_kamar' => 'Blok B - Kamar 04',
                'status' => 'aktif',
                'saldo' => 350000.00,
                'va_number' => '88001081234567',
                'limit_harian' => 100000.00,
                'kontak_keluarga' => '081234567890 (Istri - Siti)',
            ],
            [
                'no_registrasi' => 'WBP-2026-0002',
                'nik' => '3201123456780002',
                'nama' => 'Ahmad Fauzi bin Ridwan',
                'lapas_asal' => 'Lapas Kelas I',
                'blok_kamar' => 'Blok A - Kamar 02',
                'status' => 'aktif',
                'saldo' => 125000.00,
                'va_number' => '88001081234568',
                'limit_harian' => 75000.00,
                'kontak_keluarga' => '085712349999 (Ibu - Maryam)',
            ],
            [
                'no_registrasi' => 'WBP-2026-0003',
                'nik' => '3201123456780003',
                'nama' => 'Hendra Setiawan bin Joko',
                'lapas_asal' => 'Lapas Kelas I',
                'blok_kamar' => 'Blok C - Kamar 10',
                'status' => 'aktif',
                'saldo' => 45000.00,
                'va_number' => '88001081234569',
                'limit_harian' => 50000.00,
                'kontak_keluarga' => '087811223344 (Kakak - Dedi)',
            ],
            [
                'no_registrasi' => 'WBP-2026-0004',
                'nik' => '3201123456780004',
                'nama' => 'Rian Hidayat bin Subagyo',
                'lapas_asal' => 'Rutan Kelas I',
                'blok_kamar' => 'Blok Khusus Mapenaling',
                'status' => 'aktif',
                'saldo' => 500000.00,
                'va_number' => '88001081234570',
                'limit_harian' => 100000.00,
                'kontak_keluarga' => '089912340000 (Ayah - Subagyo)',
            ],
        ];

        foreach ($inmates as $data) {
            Inmate::updateOrCreate(['no_registrasi' => $data['no_registrasi']], $data);
        }

        // 3. Sample Products Koperasi Lapas
        $products = [
            [
                'barcode' => '8999909001',
                'nama_barang' => 'Indomie Goreng Original',
                'kategori' => 'Makanan & Minuman',
                'harga' => 3500.00,
                'stok' => 240,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909002',
                'nama_barang' => 'Air Mineral Le Minerale 600ml',
                'kategori' => 'Makanan & Minuman',
                'harga' => 4000.00,
                'stok' => 180,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909003',
                'nama_barang' => 'Kopi Kapal Api Special Mix Sachet',
                'kategori' => 'Makanan & Minuman',
                'harga' => 2000.00,
                'stok' => 300,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909004',
                'nama_barang' => 'Sabun Mandi Lifebuoy Total 10',
                'kategori' => 'Perlengkapan Mandi',
                'harga' => 5000.00,
                'stok' => 95,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909005',
                'nama_barang' => 'Pasta Gigi Pepsodent 120gr',
                'kategori' => 'Perlengkapan Mandi',
                'harga' => 8500.00,
                'stok' => 60,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909006',
                'nama_barang' => 'Shampoo Clear Men Cool Sport 70ml',
                'kategori' => 'Perlengkapan Mandi',
                'harga' => 12000.00,
                'stok' => 45,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909007',
                'nama_barang' => 'Biskuit Roma Kelapa 300gr',
                'kategori' => 'Makanan & Minuman',
                'harga' => 11000.00,
                'stok' => 50,
                'is_active' => true,
            ],
            [
                'barcode' => '8999909008',
                'nama_barang' => 'Deterjen Daia Putih 290gr',
                'kategori' => 'Kebutuhan Kamar',
                'harga' => 6000.00,
                'stok' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['barcode' => $p['barcode']], $p);
        }

        // 4. Sample Transactions
        $budi = Inmate::where('no_registrasi', 'WBP-2026-0001')->first();
        if ($budi) {
            // Sample Top Up via Loket
            Transaction::create([
                'invoice_no' => 'TOPUP-20261017-001',
                'inmate_id' => $budi->id,
                'type' => 'topup',
                'amount' => 200000.00,
                'balance_before' => 150000.00,
                'balance_after' => 350000.00,
                'payment_method' => 'cash_counter',
                'reference_id' => 'LOKET-KUNJUNGAN-01',
                'status' => 'success',
                'cashier_name' => 'Petugas Loket Kunjungan',
                'notes' => 'Setoran titipan dari Istri (Siti)',
                'created_at' => now()->subHours(3),
            ]);

            // Sample Purchase at Canteen
            $trx = Transaction::create([
                'invoice_no' => 'TRX-20261017-001',
                'inmate_id' => $budi->id,
                'type' => 'purchase',
                'amount' => 15500.00,
                'balance_before' => 365500.00,
                'balance_after' => 350000.00,
                'payment_method' => 'fingerprint',
                'status' => 'success',
                'cashier_name' => 'Kasir Koperasi 1',
                'notes' => 'Otorisasi Sidik Jari U.are.U 4500 (Score: 0)',
                'created_at' => now()->subHour(),
            ]);

            $p1 = Product::where('barcode', '8999909001')->first();
            $p2 = Product::where('barcode', '8999909002')->first();
            $p3 = Product::where('barcode', '8999909003')->first();

            if ($p1 && $p2 && $p3) {
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $p1->id,
                    'nama_barang' => $p1->nama_barang,
                    'harga_satuan' => $p1->harga,
                    'qty' => 2,
                    'subtotal' => 7000.00,
                ]);
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $p2->id,
                    'nama_barang' => $p2->nama_barang,
                    'harga_satuan' => $p2->harga,
                    'qty' => 1,
                    'subtotal' => 4000.00,
                ]);
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $p3->id,
                    'nama_barang' => $p3->nama_barang,
                    'harga_satuan' => $p3->harga,
                    'qty' => 2,
                    'subtotal' => 4000.00,
                ]);
            }
        }
    }
}
