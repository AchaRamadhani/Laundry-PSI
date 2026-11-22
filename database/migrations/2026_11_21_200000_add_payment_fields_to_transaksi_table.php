<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('status_pembayaran', 20)->default('belum_lunas')->after('status_cucian');
            $table->string('metode_pembayaran', 20)->nullable()->after('status_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'metode_pembayaran']);
        });
    }
};
