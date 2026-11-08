<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('tracking_token', 12)->nullable()->after('no_nota');
        });

        DB::table('transaksi')
            ->select('no_nota')
            ->whereNull('tracking_token')
            ->chunkById(100, function ($transaksi) {
                foreach ($transaksi as $item) {
                    do {
                        $token = Str::upper(Str::random(12));
                        $alreadyUsed = DB::table('transaksi')->where('tracking_token', $token)->exists();
                    } while ($alreadyUsed);

                    DB::table('transaksi')
                        ->where('no_nota', $item->no_nota)
                        ->update(['tracking_token' => $token]);
                }
            }, 'no_nota', 'no_nota');

        Schema::table('transaksi', function (Blueprint $table) {
            $table->unique('tracking_token');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn('tracking_token');
        });
    }
};
