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
        // Ubah enum status menjadi varchar(30) agar fleksibel mendukung isolasi/register F dan bebas
        DB::statement("ALTER TABLE inmates MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'aktif'");

        Schema::table('inmates', function (Blueprint $table) {
            $table->text('alasan_status')->nullable()->after('status');
            $table->date('tanggal_masuk')->nullable()->after('alasan_status');
            $table->date('tanggal_ekspirasi')->nullable()->after('tanggal_masuk'); // perkiraan tanggal bebas
            $table->timestamp('payout_at')->nullable()->after('tanggal_ekspirasi'); // tanggal pencairan saldo saat bebas
            $table->decimal('payout_amount', 15, 2)->default(0.00)->after('payout_at');
            $table->string('payout_officer')->nullable()->after('payout_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inmates', function (Blueprint $table) {
            $table->dropColumn([
                'alasan_status',
                'tanggal_masuk',
                'tanggal_ekspirasi',
                'payout_at',
                'payout_amount',
                'payout_officer'
            ]);
        });

        DB::statement("ALTER TABLE inmates MODIFY COLUMN status ENUM('aktif', 'mutasi', 'bebas') NOT NULL DEFAULT 'aktif'");
    }
};
