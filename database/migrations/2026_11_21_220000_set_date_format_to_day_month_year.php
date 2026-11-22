<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('laundry_settings')->update(['date_format' => 'd/m/Y']);
    }

    public function down(): void
    {
        DB::table('laundry_settings')
            ->where('date_format', 'd/m/Y')
            ->update(['date_format' => 'd-m-Y']);
    }
};
