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
        // 1. Update Inmates table
        Schema::table('inmates', function (Blueprint $table) {
            $table->string('upt_code', 30)->default('LPS-01')->after('lapas_asal');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('synced')->after('status');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
        });

        // 2. Update Transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('upt_code', 30)->default('LPS-01')->after('invoice_no');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('synced')->after('status');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
        });

        // 3. Update Inmate Transfers table
        Schema::table('inmate_transfers', function (Blueprint $table) {
            $table->string('upt_code_asal', 30)->nullable()->after('lapas_asal');
            $table->string('upt_code_tujuan', 30)->nullable()->after('lapas_tujuan');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('synced')->after('alasan');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
        });

        // 4. Create UPT Settings table
        Schema::create('upt_settings', function (Blueprint $table) {
            $table->id();
            $table->string('upt_code', 30)->default('LPS-01')->unique();
            $table->string('upt_name')->default('Lapas Kelas I Tangerang');
            $table->enum('server_role', ['upt_local', 'central'])->default('upt_local');
            $table->string('central_api_url')->nullable()->default('http://127.0.0.1:8001/api/v1');
            $table->string('central_api_key')->nullable()->default('uangpas_secret_token_lapas');
            $table->boolean('auto_sync')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamps();
        });

        // Seed default local UPT setting
        DB::table('upt_settings')->insert([
            'upt_code' => 'LPS-01',
            'upt_name' => 'Lapas Kelas I Tangerang',
            'server_role' => 'upt_local',
            'central_api_url' => 'http://127.0.0.1:8001/api/v1',
            'central_api_key' => 'uangpas_secret_token_lapas',
            'auto_sync' => true,
            'last_sync_at' => now(),
            'last_backup_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upt_settings');

        Schema::table('inmate_transfers', function (Blueprint $table) {
            $table->dropColumn(['upt_code_asal', 'upt_code_tujuan', 'sync_status', 'synced_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['upt_code', 'sync_status', 'synced_at']);
        });

        Schema::table('inmates', function (Blueprint $table) {
            $table->dropColumn(['upt_code', 'sync_status', 'synced_at']);
        });
    }
};

