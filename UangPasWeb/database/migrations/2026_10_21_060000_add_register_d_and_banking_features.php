<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah pengaturan Buku Register D & Mode Aplikasi ke upt_settings
        Schema::table('upt_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('upt_settings', 'app_mode')) {
                $table->string('app_mode', 20)->default('wallet_only')->after('upt_name'); // 'wallet_only' atau 'full_pos'
            }
            if (!Schema::hasColumn('upt_settings', 'register_d_officer_name')) {
                $table->string('register_d_officer_name')->nullable()->default('Petugas Pengelola Register D')->after('kepala_nip');
            }
            if (!Schema::hasColumn('upt_settings', 'register_d_officer_nip')) {
                $table->string('register_d_officer_nip', 50)->nullable()->default('198801012012011001')->after('register_d_officer_name');
            }
            if (!Schema::hasColumn('upt_settings', 'kplp_nama')) {
                $table->string('kplp_nama')->nullable()->default('Bpk. Kepala Pengamanan Lapas (Ka. KPLP)')->after('register_d_officer_nip');
            }
            if (!Schema::hasColumn('upt_settings', 'kplp_nip')) {
                $table->string('kplp_nip', 50)->nullable()->default('198205052006041003')->after('kplp_nama');
            }
            if (!Schema::hasColumn('upt_settings', 'bank_name_escrow')) {
                $table->string('bank_name_escrow')->nullable()->default('Bank BNI - Rekening Giro Titipan Uang Lapas')->after('koperasi_name');
            }
            if (!Schema::hasColumn('upt_settings', 'bank_account_escrow')) {
                $table->string('bank_account_escrow', 50)->nullable()->default('0123-4567-8901')->after('bank_name_escrow');
            }
        });

        // 2. Tambah Nomor Register D dan data Penjamin / Keluarga ke inmates
        Schema::table('inmates', function (Blueprint $table) {
            if (!Schema::hasColumn('inmates', 'register_d_number')) {
                $table->string('register_d_number', 50)->nullable()->after('no_registrasi');
            }
            if (!Schema::hasColumn('inmates', 'penjamin_nama')) {
                $table->string('penjamin_nama')->nullable()->after('kontak_keluarga');
            }
            if (!Schema::hasColumn('inmates', 'penjamin_hubungan')) {
                $table->string('penjamin_hubungan', 50)->nullable()->after('penjamin_nama');
            }
            if (!Schema::hasColumn('inmates', 'penjamin_nik')) {
                $table->string('penjamin_nik', 20)->nullable()->after('penjamin_hubungan');
            }
            if (!Schema::hasColumn('inmates', 'penjamin_telepon')) {
                $table->string('penjamin_telepon', 30)->nullable()->after('penjamin_nik');
            }
            if (!Schema::hasColumn('inmates', 'penjamin_alamat')) {
                $table->text('penjamin_alamat')->nullable()->after('penjamin_telepon');
            }
        });

        // 3. Tambah metadata administratif Register D & Penyetor ke transactions
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('transactions', 'sender_nik')) {
                $table->string('sender_nik', 20)->nullable()->after('sender_name');
            }
            if (!Schema::hasColumn('transactions', 'sender_phone')) {
                $table->string('sender_phone', 30)->nullable()->after('sender_nik');
            }
            if (!Schema::hasColumn('transactions', 'sender_relation')) {
                $table->string('sender_relation', 50)->nullable()->after('sender_phone');
            }
            if (!Schema::hasColumn('transactions', 'category')) {
                $table->string('category', 40)->default('deposit_loket')->after('sender_relation');
            }
            if (!Schema::hasColumn('transactions', 'officer_name')) {
                $table->string('officer_name')->nullable()->after('cashier_name');
            }
            if (!Schema::hasColumn('transactions', 'receipt_number')) {
                $table->string('receipt_number', 50)->nullable()->after('reference_id');
            }
        });

        // Otomatis generate nomor register_d_number untuk WBP yang sudah ada
        $existingInmates = DB::table('inmates')->whereNull('register_d_number')->get();
        foreach ($existingInmates as $idx => $inmate) {
            $regD = 'REG-D/' . date('Y') . '/' . str_pad($inmate->id, 4, '0', STR_PAD_LEFT);
            DB::table('inmates')->where('id', $inmate->id)->update([
                'register_d_number' => $regD,
                'penjamin_nama' => $inmate->kontak_keluarga ? 'Keluarga (' . $inmate->kontak_keluarga . ')' : null,
                'penjamin_hubungan' => 'Keluarga Kandung'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upt_settings', function (Blueprint $table) {
            $cols = ['app_mode', 'register_d_officer_name', 'register_d_officer_nip', 'kplp_nama', 'kplp_nip', 'bank_name_escrow', 'bank_account_escrow'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('upt_settings', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('inmates', function (Blueprint $table) {
            $cols = ['register_d_number', 'penjamin_nama', 'penjamin_hubungan', 'penjamin_nik', 'penjamin_telepon', 'penjamin_alamat'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('inmates', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            $cols = ['sender_name', 'sender_nik', 'sender_phone', 'sender_relation', 'category', 'officer_name', 'receipt_number'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('transactions', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
