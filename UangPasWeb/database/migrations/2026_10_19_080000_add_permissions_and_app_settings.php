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
        // 1. Add permissions column to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->text('permissions')->nullable()->after('role');
            }
        });

        // 2. Add comprehensive general application settings to upt_settings table
        Schema::table('upt_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('upt_settings', 'kanwil_name')) {
                $table->string('kanwil_name')->nullable()->default('Kantor Wilayah Ditjenpas Banten')->after('upt_name');
            }
            if (!Schema::hasColumn('upt_settings', 'kepala_nama')) {
                $table->string('kepala_nama')->nullable()->default('Bpk. Kepala Lapas Kelas I, Bc.IP., S.H.')->after('kanwil_name');
            }
            if (!Schema::hasColumn('upt_settings', 'kepala_nip')) {
                $table->string('kepala_nip', 50)->nullable()->default('198002022005011002')->after('kepala_nama');
            }
            if (!Schema::hasColumn('upt_settings', 'alamat_lengkap')) {
                $table->text('alamat_lengkap')->nullable()->after('kepala_nip');
            }
            if (!Schema::hasColumn('upt_settings', 'kontak_telepon')) {
                $table->string('kontak_telepon', 50)->nullable()->default('(021) 5523456 / 0812-3456-7890')->after('alamat_lengkap');
            }
            if (!Schema::hasColumn('upt_settings', 'kontak_email')) {
                $table->string('kontak_email')->nullable()->default('lapas1tangerang@kemenkumham.go.id')->after('kontak_telepon');
            }
            if (!Schema::hasColumn('upt_settings', 'koperasi_name')) {
                $table->string('koperasi_name')->nullable()->default('Koperasi Pengayoman Pegawai Lapas (KPPL)')->after('kontak_email');
            }
            if (!Schema::hasColumn('upt_settings', 'default_limit_harian')) {
                $table->decimal('default_limit_harian', 12, 2)->default(100000)->after('koperasi_name');
            }
            if (!Schema::hasColumn('upt_settings', 'min_topup_amount')) {
                $table->decimal('min_topup_amount', 12, 2)->default(10000)->after('default_limit_harian');
            }
            if (!Schema::hasColumn('upt_settings', 'max_balance_limit')) {
                $table->decimal('max_balance_limit', 12, 2)->default(2000000)->after('min_topup_amount');
            }
            if (!Schema::hasColumn('upt_settings', 'jam_buka_pos')) {
                $table->string('jam_buka_pos', 10)->default('08:00')->after('max_balance_limit');
            }
            if (!Schema::hasColumn('upt_settings', 'jam_tutup_pos')) {
                $table->string('jam_tutup_pos', 10)->default('16:00')->after('jam_buka_pos');
            }
            if (!Schema::hasColumn('upt_settings', 'kiosk_poll_interval')) {
                $table->integer('kiosk_poll_interval')->default(5)->after('jam_tutup_pos');
            }
            if (!Schema::hasColumn('upt_settings', 'enable_biometric_mandatory')) {
                $table->boolean('enable_biometric_mandatory')->default(true)->after('kiosk_poll_interval');
            }
        });

        // Set realistic defaults for existing upt_settings
        DB::table('upt_settings')->update([
            'kanwil_name' => 'Kantor Wilayah Ditjenpas Banten',
            'kepala_nama' => 'Bpk. Kepala Lapas Kelas I, Bc.IP., S.H.',
            'kepala_nip' => '198002022005011002',
            'alamat_lengkap' => 'Jl. Veteran No. 1, Kota Tangerang, Banten 15118',
            'kontak_telepon' => '(021) 5523456 / 0812-3456-7890',
            'kontak_email' => 'lapas1tangerang@kemenkumham.go.id',
            'koperasi_name' => 'Koperasi Pengayoman Pegawai Lapas (KPPL)',
            'default_limit_harian' => 100000,
            'min_topup_amount' => 10000,
            'max_balance_limit' => 2000000,
            'jam_buka_pos' => '08:00',
            'jam_tutup_pos' => '16:00',
            'kiosk_poll_interval' => 5,
            'enable_biometric_mandatory' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });

        Schema::table('upt_settings', function (Blueprint $table) {
            $cols = [
                'kanwil_name', 'kepala_nama', 'kepala_nip', 'alamat_lengkap',
                'kontak_telepon', 'kontak_email', 'koperasi_name', 'default_limit_harian',
                'min_topup_amount', 'max_balance_limit', 'jam_buka_pos', 'jam_tutup_pos',
                'kiosk_poll_interval', 'enable_biometric_mandatory'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('upt_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
