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
        // 1. Inmates / Warga Binaan Pemasyarakatan (WBP)
        Schema::create('inmates', function (Blueprint $table) {
            $table->id();
            $table->string('no_registrasi')->unique(); // e.g. WBP-2026-0001
            $table->string('nik', 20)->nullable();
            $table->string('nama');
            $table->string('lapas_asal')->default('Lapas Kelas I');
            $table->string('blok_kamar')->default('Blok A-01');
            $table->enum('status', ['aktif', 'mutasi', 'bebas'])->default('aktif');
            $table->decimal('saldo', 15, 2)->default(0.00);
            $table->string('va_number', 30)->unique()->nullable(); // Virtual Account keluarga
            $table->decimal('limit_harian', 15, 2)->default(100000.00); // Batas belanja harian
            $table->longText('fingerprint_template')->nullable(); // FMD XML dari U.are.U 4500
            $table->longText('fingerprint_image')->nullable(); // Base64 data image preview
            $table->string('kontak_keluarga')->nullable();
            $table->timestamps();
        });

        // 2. Products / Barang Koperasi Lapas
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 50)->unique();
            $table->string('nama_barang');
            $table->string('kategori')->default('Umum'); // Makanan & Minuman, Mandi, dsb
            $table->decimal('harga', 15, 2);
            $table->integer('stok')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Transactions / Riwayat Keuangan
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique();
            $table->foreignId('inmate_id')->constrained('inmates')->onDelete('cascade');
            $table->enum('type', ['purchase', 'topup', 'transfer_in', 'transfer_out'])->default('purchase');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2)->default(0.00);
            $table->decimal('balance_after', 15, 2)->default(0.00);
            $table->enum('payment_method', ['fingerprint', 'cash_counter', 'bank_va', 'system'])->default('fingerprint');
            $table->string('reference_id')->nullable(); // Ref Payment Gateway / Loket
            $table->enum('status', ['success', 'pending', 'failed', 'cancelled'])->default('success');
            $table->string('cashier_name')->default('Kasir Koperasi');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Transaction Items / Rincian Belanja
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('nama_barang');
            $table->decimal('harga_satuan', 15, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 5. Inmate Transfers / Riwayat Mutasi Lapas
        Schema::create('inmate_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inmate_id')->constrained('inmates')->onDelete('cascade');
            $table->string('lapas_asal');
            $table->string('lapas_tujuan');
            $table->string('blok_asal')->nullable();
            $table->string('blok_tujuan')->nullable();
            $table->date('tanggal_mutasi');
            $table->decimal('saldo_dibawa', 15, 2)->default(0.00);
            $table->string('petugas_mutasi')->default('Admin Lapas');
            $table->text('alasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inmate_transfers');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('inmates');
    }
};
