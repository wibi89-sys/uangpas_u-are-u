<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Suppliers / Distributor Rekanan Koperasi
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier', 50)->unique();
            $table->string('nama_supplier');
            $table->string('kontak_person')->nullable();
            $table->string('no_telp', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->integer('termin_hari')->default(0); // 0 = Cash/COD
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Enhance Products Table
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('harga_beli', 15, 2)->default(0.00)->after('kategori'); // HPP / Modal
            $table->string('satuan', 30)->default('Pcs')->after('harga');
            $table->integer('min_stock')->default(10)->after('stok');
            $table->foreignId('supplier_id')->nullable()->after('min_stock')->constrained('suppliers')->nullOnDelete();
            $table->string('lokasi_rak', 50)->nullable()->after('supplier_id');
        });

        // 3. Stock Purchases / Faktur Barang Masuk Restock
        Schema::create('stock_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no', 50)->unique();
            $table->string('no_faktur_supplier', 100)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('tanggal_masuk');
            $table->decimal('total_nominal', 15, 2)->default(0.00);
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
            $table->string('petugas_penerima', 100)->default('Admin Gudang');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 4. Stock Purchase Items
        Schema::create('stock_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_purchase_id')->constrained('stock_purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('qty');
            $table->decimal('harga_beli_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 5. Stock Opnames / Berita Acara Audit Fisik
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_no', 50)->unique();
            $table->date('tanggal_opname');
            $table->string('petugas_opname', 100);
            $table->string('auditor_name', 100)->nullable();
            $table->integer('total_item')->default(0);
            $table->integer('total_selisih_qty')->default(0);
            $table->decimal('total_selisih_nominal', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'adjusted', 'cancelled'])->default('adjusted');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 6. Stock Opname Items
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('stok_sistem');
            $table->integer('stok_fisik');
            $table->integer('selisih_qty'); // fisik - sistem
            $table->decimal('hpp', 15, 2)->default(0.00);
            $table->decimal('selisih_nominal', 15, 2)->default(0.00);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 7. Stock Mutations / Kartu Stok Ledger
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('type', ['purchase', 'sale', 'opname_adj', 'damage', 'initial'])->default('sale');
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('stok_awal');
            $table->integer('stok_akhir');
            $table->string('reference_no', 100)->nullable();
            $table->string('petugas', 100)->default('Sistem');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('stock_purchase_items');
        Schema::dropIfExists('stock_purchases');

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['harga_beli', 'satuan', 'min_stock', 'supplier_id', 'lokasi_rak']);
        });

        Schema::dropIfExists('suppliers');
    }
};
