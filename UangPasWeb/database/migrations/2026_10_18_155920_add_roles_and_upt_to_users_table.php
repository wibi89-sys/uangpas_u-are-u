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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('admin_koperasi')->after('email');
            // Role list: kanwil_admin, kalapas, admin_koperasi, petugas_loket, kasir, auditor
            $table->string('upt_code', 30)->default('LPS-01')->after('role');
            $table->string('phone', 25)->nullable()->after('upt_code');
            $table->string('nip', 30)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'upt_code', 'phone', 'nip', 'is_active']);
        });
    }
};
