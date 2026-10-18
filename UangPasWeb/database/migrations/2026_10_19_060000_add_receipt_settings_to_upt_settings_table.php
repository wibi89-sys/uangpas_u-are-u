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
        Schema::table('upt_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('upt_settings', 'receipt_store_name')) {
                $table->string('receipt_store_name')->default('KOPERASI PENGAYOMAN LAPAS')->after('upt_name');
            }
            if (!Schema::hasColumn('upt_settings', 'receipt_address')) {
                $table->string('receipt_address')->nullable()->default('Jalan Pemasyarakatan No. 1')->after('receipt_store_name');
            }
            if (!Schema::hasColumn('upt_settings', 'receipt_phone')) {
                $table->string('receipt_phone', 50)->nullable()->default('(021) 5523456')->after('receipt_address');
            }
            if (!Schema::hasColumn('upt_settings', 'receipt_footer')) {
                $table->text('receipt_footer')->nullable()->after('receipt_phone');
            }
            if (!Schema::hasColumn('upt_settings', 'receipt_paper_width')) {
                $table->enum('receipt_paper_width', ['58mm', '80mm'])->default('58mm')->after('receipt_footer');
            }
            if (!Schema::hasColumn('upt_settings', 'receipt_auto_print')) {
                $table->boolean('receipt_auto_print')->default(false)->after('receipt_paper_width');
            }
            if (!Schema::hasColumn('upt_settings', 'receipt_show_inmate_balance')) {
                $table->boolean('receipt_show_inmate_balance')->default(true)->after('receipt_auto_print');
            }
        });

        // Set default values for existing rows
        DB::table('upt_settings')->update([
            'receipt_store_name' => 'KOPERASI PENGAYOMAN LAPAS',
            'receipt_address' => 'Jalan Pemasyarakatan No. 1',
            'receipt_phone' => '(021) 5523456',
            'receipt_footer' => "Terima Kasih Atas Kunjungan Anda\nSimpan Struk Sebagai Bukti Belanja Sah\nUangPas - Zero Cash Solution",
            'receipt_paper_width' => '58mm',
            'receipt_auto_print' => false,
            'receipt_show_inmate_balance' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upt_settings', function (Blueprint $table) {
            $cols = [
                'receipt_store_name',
                'receipt_address',
                'receipt_phone',
                'receipt_footer',
                'receipt_paper_width',
                'receipt_auto_print',
                'receipt_show_inmate_balance',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('upt_settings', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
