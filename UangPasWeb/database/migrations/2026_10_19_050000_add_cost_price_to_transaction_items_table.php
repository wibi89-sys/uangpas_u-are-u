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
        if (!Schema::hasColumn('transaction_items', 'harga_beli')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->decimal('harga_beli', 15, 2)->default(0.00)->after('harga_satuan');
            });

            // Backfill existing transaction_items with products.harga_beli
            DB::statement("
                UPDATE transaction_items ti
                INNER JOIN products p ON ti.product_id = p.id
                SET ti.harga_beli = p.harga_beli
                WHERE ti.product_id IS NOT NULL AND p.harga_beli > 0
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('transaction_items', 'harga_beli')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->dropColumn('harga_beli');
            });
        }
    }
};
