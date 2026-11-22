<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracking tokens are stateless and must never be written to the database.
    }

    public function down(): void
    {
        if (! Schema::hasColumn('transaksi', 'tracking_token')) {
            return;
        }

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn('tracking_token');
        });
    }
};
