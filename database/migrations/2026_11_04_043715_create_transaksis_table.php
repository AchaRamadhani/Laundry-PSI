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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->string('no_nota')->primary();

            $table->foreignId('pelanggan_id')
                ->constrained('pelanggan');

            $table->foreignId('id_admin')
                ->constrained('users');

            $table->date('tanggal_masuk');
            $table->dateTime('estimasi_selesai')->nullable();   
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->string('status_cucian')->default('antrean');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
