<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\StockPurchase;
use App\Models\StockPurchaseItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockMutation;

class RetailInventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Suppliers
        $sup1 = Supplier::updateOrCreate(
            ['kode_supplier' => 'SUP-001'],
            [
                'nama_supplier' => 'PT Indomarco Adi Prima',
                'kontak_person' => 'Budi Santoso',
                'no_telp' => '0812-3456-7890',
                'email' => 'order@indomarco-dist.co.id',
                'alamat' => 'Kawasan Industri Jatake, Tangerang',
                'termin_hari' => 14,
                'is_active' => true,
            ]
        );

        $sup2 = Supplier::updateOrCreate(
            ['kode_supplier' => 'SUP-002'],
            [
                'nama_supplier' => 'CV Sumber Berkah Mandiri',
                'kontak_person' => 'Siti Rahma',
                'no_telp' => '0813-9876-5432',
                'email' => 'sales@sumberberkah.com',
                'alamat' => 'Jl. Daan Mogot Km. 19, Kalideres, Jakarta Barat',
                'termin_hari' => 7,
                'is_active' => true,
            ]
        );

        $sup3 = Supplier::updateOrCreate(
            ['kode_supplier' => 'SUP-003'],
            [
                'nama_supplier' => 'PT Mayora Kencana Distribusi',
                'kontak_person' => 'Hendra Kurnia',
                'no_telp' => '0811-2233-4455',
                'email' => 'distribution@mayora.co.id',
                'alamat' => 'Jl. Tomang Raya No. 21, Jakarta Barat',
                'termin_hari' => 30,
                'is_active' => true,
            ]
        );

        // 2. Update existing products with HPP, satuan, min_stock, supplier_id, and lokasi_rak
        $productUpdates = [
            '8999909001' => ['harga_beli' => 2800.00, 'satuan' => 'Bungkus', 'min_stock' => 50, 'supplier_id' => $sup1->id, 'lokasi_rak' => 'Rak A-01'],
            '8999909002' => ['harga_beli' => 3100.00, 'satuan' => 'Botol', 'min_stock' => 40, 'supplier_id' => $sup1->id, 'lokasi_rak' => 'Pendingin 01'],
            '8999909003' => ['harga_beli' => 1500.00, 'satuan' => 'Sachet', 'min_stock' => 60, 'supplier_id' => $sup1->id, 'lokasi_rak' => 'Rak A-02'],
            '8999909004' => ['harga_beli' => 4100.00, 'satuan' => 'Batang', 'min_stock' => 25, 'supplier_id' => $sup2->id, 'lokasi_rak' => 'Rak B-01'],
            '8999909005' => ['harga_beli' => 7000.00, 'satuan' => 'Tube', 'min_stock' => 20, 'supplier_id' => $sup2->id, 'lokasi_rak' => 'Rak B-02'],
            '8999909006' => ['harga_beli' => 10000.00, 'satuan' => 'Botol', 'min_stock' => 15, 'supplier_id' => $sup2->id, 'lokasi_rak' => 'Rak B-03'],
            '8999909007' => ['harga_beli' => 9200.00, 'satuan' => 'Bungkus', 'min_stock' => 20, 'supplier_id' => $sup3->id, 'lokasi_rak' => 'Rak A-03'],
            '8999909008' => ['harga_beli' => 5000.00, 'satuan' => 'Bungkus', 'min_stock' => 20, 'supplier_id' => $sup2->id, 'lokasi_rak' => 'Rak C-01'],
            '8999909009' => ['harga_beli' => 2500.00, 'satuan' => 'Pcs', 'min_stock' => 30, 'supplier_id' => $sup2->id, 'lokasi_rak' => 'Rak C-02'],
            '8999909010' => ['harga_beli' => 31000.00, 'satuan' => 'Bungkus', 'min_stock' => 30, 'supplier_id' => $sup1->id, 'lokasi_rak' => 'Etalase Kasir'],
        ];

        foreach ($productUpdates as $barcode => $data) {
            $p = Product::where('barcode', $barcode)->first();
            if ($p) {
                $p->update($data);

                // Initial mutation if empty
                if ($p->stockMutations()->count() === 0) {
                    StockMutation::record(
                        $p->id,
                        'initial',
                        $p->stok,
                        0,
                        0,
                        $p->stok,
                        'INIT-STOK',
                        'Sistem Migrasi',
                        'Saldo stok awal barang koperasi'
                    );
                }
            }
        }

        // 3. Seed Sample Restock (Stock Purchase)
        $pIndomie = Product::where('barcode', '8999909001')->first();
        $pKopi = Product::where('barcode', '8999909003')->first();

        if ($pIndomie && $pKopi) {
            $purchase = StockPurchase::updateOrCreate(
                ['purchase_no' => 'PO-202610-001'],
                [
                    'no_faktur_supplier' => 'INV-IND/26/10/8812',
                    'supplier_id' => $sup1->id,
                    'tanggal_masuk' => now()->subDays(2),
                    'total_nominal' => 2800 * 100 + 1500 * 100,
                    'status' => 'completed',
                    'petugas_penerima' => 'Ahmad Fauzi (Pengurus Koperasi)',
                    'catatan' => 'Penerimaan restock mingguan makanan & minuman reguler.',
                ]
            );

            StockPurchaseItem::firstOrCreate(
                ['stock_purchase_id' => $purchase->id, 'product_id' => $pIndomie->id],
                [
                    'qty' => 100,
                    'harga_beli_satuan' => 2800.00,
                    'subtotal' => 280000.00,
                ]
            );

            StockPurchaseItem::firstOrCreate(
                ['stock_purchase_id' => $purchase->id, 'product_id' => $pKopi->id],
                [
                    'qty' => 100,
                    'harga_beli_satuan' => 1500.00,
                    'subtotal' => 150000.00,
                ]
            );
        }

        // 4. Seed Sample Stock Opname
        if ($pIndomie) {
            $opname = StockOpname::updateOrCreate(
                ['opname_no' => 'SO-202610-001'],
                [
                    'tanggal_opname' => now()->subDay(),
                    'petugas_opname' => 'Ahmad Fauzi (Admin Koperasi)',
                    'auditor_name' => 'Siti Nurhaliza (Auditor Internal)',
                    'total_item' => 1,
                    'total_selisih_qty' => -2,
                    'total_selisih_nominal' => -5600.00,
                    'status' => 'adjusted',
                    'catatan' => 'Audit stok fisik rutin akhir pekan. Ditemukan 2 bungkus kemasan rusak.',
                ]
            );

            StockOpnameItem::firstOrCreate(
                ['stock_opname_id' => $opname->id, 'product_id' => $pIndomie->id],
                [
                    'stok_sistem' => 242,
                    'stok_fisik' => 240,
                    'selisih_qty' => -2,
                    'hpp' => 2800.00,
                    'selisih_nominal' => -5600.00,
                    'keterangan' => 'Kemasan sobek digigit hama tikus gudang',
                ]
            );
        }
    }
}
