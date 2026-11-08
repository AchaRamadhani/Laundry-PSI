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
        Schema::create('paket_laundry', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket');
            $table->string('kategori');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_mulai', 12, 2);
            $table->integer('durasi');
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_laundry');
    }
};
