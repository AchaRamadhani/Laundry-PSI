<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_settings', function (Blueprint $table) {
            $table->id();
            $table->string('laundry_name', 120)->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->text('completion_message')->nullable();
            $table->boolean('notify_browser')->default(true);
            $table->boolean('notify_message')->default(true);
            $table->string('date_format', 10)->default('d-m-Y');
            $table->boolean('confirm_logout')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_settings');
    }
};
