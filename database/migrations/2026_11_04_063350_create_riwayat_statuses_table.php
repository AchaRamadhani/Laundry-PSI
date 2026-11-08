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
        Schema::create('status_laundry', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota');
            $table->foreign('no_nota')
                ->references('no_nota')
                ->on('transaksi')
                ->cascadeOnDelete();

            $table->foreignId('id_admin')
                ->constrained('users');

            $table->string('status');
            $table->text('catatan_status')->nullable();
            $table->timestamp('waktu_status')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_laundry');
    }
};
