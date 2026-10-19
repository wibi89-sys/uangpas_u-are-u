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
        // 1. Add fingerprint_zk_template to inmates table
        Schema::table('inmates', function (Blueprint $table) {
            if (!Schema::hasColumn('inmates', 'fingerprint_zk_template')) {
                $table->longText('fingerprint_zk_template')->nullable()->after('fingerprint_template');
            }
        });

        // 2. Add Solution device settings to upt_settings table
        Schema::table('upt_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('upt_settings', 'solution_device_ip')) {
                $table->string('solution_device_ip', 50)->nullable()->default('192.168.1.201');
            }
            if (!Schema::hasColumn('upt_settings', 'solution_device_port')) {
                $table->integer('solution_device_port')->default(80);
            }
            if (!Schema::hasColumn('upt_settings', 'solution_comkey')) {
                $table->integer('solution_comkey')->default(0);
            }
            if (!Schema::hasColumn('upt_settings', 'solution_device_enabled')) {
                $table->boolean('solution_device_enabled')->default(false);
            }
            if (!Schema::hasColumn('upt_settings', 'solution_device_location')) {
                $table->string('solution_device_location', 100)->default('Blok Hunian WBP');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inmates', function (Blueprint $table) {
            if (Schema::hasColumn('inmates', 'fingerprint_zk_template')) {
                $table->dropColumn('fingerprint_zk_template');
            }
        });

        Schema::table('upt_settings', function (Blueprint $table) {
            $cols = [
                'solution_device_ip',
                'solution_device_port',
                'solution_comkey',
                'solution_device_enabled',
                'solution_device_location',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('upt_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
