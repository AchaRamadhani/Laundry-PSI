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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();

            $table->string('no_nota');
            $table->foreign('no_nota')
                ->references('no_nota')
                ->on('transaksi')
                ->cascadeOnDelete();

            $table->foreignId('paket_laundry_id')
                ->constrained('paket_laundry');

            $table->decimal('berat_qty', 10, 2);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
